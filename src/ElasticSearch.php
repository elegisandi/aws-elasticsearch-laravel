<?php

namespace elegisandi\AWSElasticsearchService;

use Illuminate\Support\Facades\Log;

/**
 * Class ElasticSearch
 * @package elegisandi\AWSElasticsearchService
 */
class ElasticSearch
{
    /**
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * @var string
     */
    public $config = 'elasticsearch';

    /**
     * ElasticSearchService constructor.
     */
    public function __construct()
    {
        $this->client = $this->buildClient();
    }

    /**
     * @param array $aggs
     * @param string $type
     * @param string $index
     * @return array
     */
    public function aggregations($aggs = [], $type = 'click', $index = 'clicktracker')
    {
        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'size' => 0,
                'aggs' => $aggs
            ],
        ];

        $aggregations = null;

        try {
            $aggregations = $this->client->search($params);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                Log::debug($e);
            }
        }

        return $aggregations;
    }


    /**
     * @param array $query
     * @param array $options [sort, size, from]
     * @param $range
     * @param string $type
     * @param string $index
     * @return array
     */
    public function search(array $query = [], $options = [], $range, $type = 'click', $index = 'clicktracker')
    {
        $params = [
            'index' => $index,
            'type' => $type,
            'body' => $options,
        ];

        // convert query to collection for easier manipulation
        $query = collect($query);

        // break term and full-text queries
        // we only have one full text data - keyword

        // get properties
        $properties = $this->getMappingProperties($type);

        // get text type properties
        $text_type_props = $this->getMappingPropertiesByType($properties, 'text');

        // get keyword type properties
        // we'll include ip type
        $keyword_type_props = $this->getMappingPropertiesByType($properties, ['keyword', 'ip']);

        // get boolean type properties
        $bool_type_props = $this->getMappingPropertiesByType($properties, 'boolean');

        // prepare keyword data type filter
        $term_filter = $this->setBoolQueryClause($query, $keyword_type_props, 'term', 'filter');

        // prepare text data type matching
        $full_text_match = $this->setBoolQueryClause($query, $text_type_props, 'match', 'must');

        // prepare boolean data type filter
        $bool_filter = $this->setBoolQueryClause($query, $bool_type_props, 'term', 'filter', function ($value) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        });

        // merge filters
        $filters = [];

        foreach (compact('term_filter', 'full_text_match', 'bool_filter') as $filter) {
            foreach ($filter as $occur => $type) {
                foreach ($type as $field) {
                    $filters[$occur][] = $field;
                }
            }
        }

        // set date range if not empty
        if (!empty($range)) {
            $filters['must'][] = ['range' => $range];
        }

        // set bool query if filters not empty
        if (!empty($filters)) {
            $params['body']['query'] = [
                'bool' => $filters
            ];
        }

        $hits = null;

        try {
            $hits = $this->client->search($params);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                Log::debug($e);
            }
        }

        return $hits;
    }

    /**
     * @param $query
     * @param $properties
     * @param $type
     * @param $occur
     * @param $callback
     * @return mixed
     */
    protected function setBoolQueryClause($query, $properties, $type, $occur, $callback = null)
    {
        $data = [];

        $query->only($properties)->each(function ($value, $key) use ($type, $occur, $callback, &$data) {
            $belongs = $occur;

            if ($value[0] == '!') {
                $belongs = 'must_not';
                $value = ltrim($value, '!');
            }

            $data[$belongs][] = [$type => [$key => is_callable($callback) ? $callback($value) : $value]];
        });

        return $data;
    }

    /**
     * @param $properties
     * @param string|array $type
     * @return mixed
     */
    protected function getMappingPropertiesByType($properties, $type)
    {
        $types = is_string($type) ? [$type] : $type;

        return $properties->filter(function ($field) use ($types) {
            return in_array($field['type'], $types);
        })->keys()->all();
    }

    /**
     * @param string $type
     * @return \Illuminate\Support\Collection
     */
    protected function getMappingProperties($type)
    {
        return collect(config($this->config)['mappings'][$type]['properties']);
    }

    /**
     * @param $id
     * @param string $type
     * @param string $index
     * @return array
     */
    public function getDocument($id, $type = 'click', $index = 'clicktracker')
    {
        $params = array_filter(compact('index', 'type', 'id'));

        return $this->client->get($params);
    }

    /**
     * @param string|array $index
     * @return array
     */
    public function getSettings($index)
    {
        return $this->client->indices()->getSettings(compact('index'));
    }

    /**
     * @param array $settings
     * @param string $index
     * @return array
     */
    public function updateSettings(array $settings, $index = 'clicktracker')
    {
        $params = [
            'index' => $index,
            'body' => [
                'settings' => $settings
            ]
        ];

        return $this->client->indices()->putSettings($params);
    }

    /**
     * @param string|array|null $index
     * @param string|null $type
     * @return array
     */
    public function getMappings($index = null, $type = null)
    {
        $params = array_filter(compact('index', 'type'));

        return $this->client->indices()->getMapping($params);
    }

    /**
     * @param array $properties
     * @param string $type
     * @param string $index
     * @return array
     */
    public function updateMappings(array $properties, $type = 'click', $index = 'clicktracker')
    {
        // Set the index and type
        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                $type => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => $properties
                ]
            ]
        ];

        return $this->client->indices()->putMapping($params);
    }

    /**
     * @param array $mappings
     * @param array $settings
     * @param string $index
     * @return array
     */
    public function createIndex(array $mappings, array $settings = [], $index = 'clicktracker')
    {
        $params = [
            'index' => $index,
            'body' => [
                'settings' => $settings,
                'mappings' => $mappings
            ]
        ];

        return $this->client->indices()->create($params);
    }

    /**
     * @param string $index
     * @return bool
     */
    public function getIndex($index = 'clicktracker')
    {
        return $this->client->indices()->get(compact('index'));
    }

    /**
     * @param string $index
     * @return array
     */
    public function deleteIndex($index = 'clicktracker')
    {
        return $this->client->indices()->delete(compact('index'));
    }

    /**
     * @return \Elasticsearch\Client
     * @credits https://github.com/aws/aws-sdk-php/issues/848#issuecomment-164592902
     */
    protected function buildClient()
    {
        $psr7Handler = \Aws\default_http_handler();
        $signer = new \Aws\Signature\SignatureV4('es', config('aws.region'));
        $credentialProvider = \Aws\Credentials\CredentialProvider::defaultProvider();

        $handler = function (array $request) use ($psr7Handler, $signer, $credentialProvider) {
            // Amazon ES listens on standard ports (443 for HTTPS, 80 for HTTP).
            $request['headers']['host'][0] = parse_url($request['headers']['host'][0])['host'];

            // Create a PSR-7 request from the array passed to the handler
            $psr7Request = new \GuzzleHttp\Psr7\Request(
                $request['http_method'],
                (new \GuzzleHttp\Psr7\Uri($request['uri']))
                    ->withScheme($request['scheme'])
                    ->withHost($request['headers']['host'][0]),
                $request['headers'],
                $request['body']
            );

            // Sign the PSR-7 request with credentials from the environment
            $signedRequest = $signer->signRequest(
                $psr7Request,
                call_user_func($credentialProvider)->wait()
            );

            // Send the signed request to Amazon ES
            /** @var \Psr\Http\Message\ResponseInterface $response */
            $response = $psr7Handler($signedRequest)->wait();

            // Convert the PSR-7 response to a RingPHP response
            return new \GuzzleHttp\Ring\Future\CompletedFutureArray([
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => $response->getBody()->detach(),
                'transfer_stats' => ['total_time' => 0],
                'effective_url' => (string)$psr7Request->getUri(),
            ]);
        };

        return \Elasticsearch\ClientBuilder::create()
            ->setHandler($handler)
            ->setHosts([env('AWS_ELASTICSEARCH_SERVICE_ENDPOINT') . ':443'])
            ->build();
    }
}
