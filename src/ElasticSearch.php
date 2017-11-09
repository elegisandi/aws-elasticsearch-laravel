<?php

namespace elegisandi\AWSElasticsearchService;

use Elasticsearch\Client;
use elegisandi\AWSElasticsearchService\Traits\ElasticSearchHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Class ElasticSearch
 * @package elegisandi\AWSElasticsearchService
 */
class ElasticSearch
{
    use ElasticSearchHelper;

    /**
     * @var Client
     */
    protected $client;

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
    private function aggregations(array $aggs, $type, $index)
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
     * @param array $range
     * @param string $type
     * @param string $index
     * @return array
     */
    private function search(array $query = [], array $options, array $range = [], $type, $index)
    {
        $params = [
            'index' => $index,
            'type' => $type,
            'body' => $options,
        ];

        // convert query to collection for easier manipulation
        $query = collect($query);

        // create query filters
        $filters = $this->setSearchQueryFilters($query, $type);

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
     * @param string $id
     * @param string $type
     * @param string $index
     * @return array
     */
    private function getDocument($id, $type, $index)
    {
        $params = array_filter(compact('index', 'type', 'id'));

        return $this->client->get($params);
    }

    /**
     * @param string|array $index
     * @return array
     */
    private function getSettings($index)
    {
        return $this->client->indices()->getSettings(compact('index'));
    }

    /**
     * @param array $settings
     * @param string $index
     * @return array
     */
    private function updateSettings(array $settings, $index)
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
     * @param string|array $index
     * @param string $type
     * @return array
     */
    private function getMappings($index, $type)
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
    private function updateMappings(array $properties, $type, $index)
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
    private function createIndex(array $mappings, array $settings, $index)
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
    private function getIndex($index)
    {
        return $this->client->indices()->get(compact('index'));
    }

    /**
     * @param string $index
     * @return array
     */
    private function deleteIndex($index)
    {
        return $this->client->indices()->delete(compact('index'));
    }

    /**
     * @return Client
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
            $response = $psr7Handler($signedRequest)->then(function (\Psr\Http\Message\ResponseInterface $response) {
                return $response;
            }, function ($error) {
                return $error['response'];
            })->wait();

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

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }
}
