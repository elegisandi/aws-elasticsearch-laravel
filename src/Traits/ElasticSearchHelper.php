<?php

namespace elegisandi\AWSElasticsearchService\Traits;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Trait ElasticSearchHelper
 * @package elegisandi\AWSElasticsearchService\Traits
 */
trait ElasticSearchHelper
{
    /**
     * @param Request $request
     * @param array $defaults
     * @param string $type
     * @return array
     */
    private function setSearchParams(Request $request, array $defaults = [], $type)
    {
        $properties = $this->getMappingProperties($type)->all();
        $fillables = array_keys($properties);
        $query = array_filter($request->only($fillables));

        if ($query && !empty($query['src']) && $query['src'] == 'all') {
            unset($query['src']);
        }

        // set date range
        $date_range = [];
        $range = $request->input('range', 'all-time');

        if ($range == 'custom') {
            $start = $request->input('start');
            $end = $request->input('end');
        } elseif ($range && $range != 'all-time') {
            extract($this->getDateRange($range));
        }

        // set default values
        $defaults = array_merge([
            'sort' => 'timestamp',
            'order' => 'desc',
            'size' => 30
        ], $defaults);

        // set sorting and paging options
        $sort = $request->input('sort', $defaults['sort']);
        $order = $request->input('order', $defaults['order']);
        $size = (int)$request->input('size', $defaults['size']);

        // parse and set start date if valid
        if (!empty($start)) {
            try {
                $date_range['timestamp']['gte'] = Carbon::parse($start)->startOfDay()->toDateTimeString();
            } catch (Exception $e) {
                $invalid_start = true;
                $start = Carbon::now()->subWeek()->toDateString();
            }
        }

        // parse and set end date if valid
        if (!empty($end)) {
            try {
                $date_range['timestamp']['lte'] = Carbon::parse($end)->endOfDay()->toDateTimeString();
            } catch (Exception $e) {
                $invalid_end = true;
                $end = Carbon::yesterday()->toDateString();
            }
        }

        // set timestamp format and timezone if any
        if (!empty($date_range['timestamp'])) {
            $date_range['timestamp']['format'] = 'yyyy-MM-dd HH:mm:ss';
            $date_range['timestamp']['time_zone'] = config('app.timezone');
        }

        // validate sort
        if (($invalid_sort = !in_array($sort, $fillables))) {
            $sort = $defaults['sort'];
        }

        // if sort field type is text, check for a keyword type field if any
        if ($properties[$sort]['type'] == 'text' && $invalid_sort = true && !empty($properties[$sort]['fields'])) {
            foreach ($properties[$sort]['fields'] as $key => $field) {
                if ($field['type'] == 'keyword') {
                    $sort = $sort . '.' . $key;
                    $invalid_sort = false;
                    break;
                }
            }
        }

        // validate order
        if ($invalid_order = !in_array($order, ['desc', 'asc'])) {
            $order = $defaults['order'];
        }

        // validate size
        if ($invalid_size = $size < 1) {
            $size = $defaults['size'];
        }

        $filters = array_filter(compact('range', 'start', 'end', 'sort', 'order', 'size'));

        // search_after
        if (($search_after = $request->input('search_after')) && !is_array($search_after)) {
            $search_after = explode(',', $search_after);
        }

        $options = array_merge(
            array_filter(compact('search_after', 'size')),
            [
                'from' => -1,
                'sort' => [
                    $sort => compact('order'),
                    '_uid' => [
                        'order' => 'asc'
                    ]
                ]
            ]
        );

        // add errors for invalid filters
        $errors = compact('invalid_start', 'invalid_end', 'invalid_sort', 'invalid_order', 'invalid_size');

        return compact('query', 'date_range', 'filters', 'options', 'errors');
    }

    /**
     * @param string $range
     * @return array
     */
    protected function getDateRange($range)
    {
        switch ($range) {
            case 'today':
                $start = Carbon::now()->startOfDay()->toDateString();
                $end = Carbon::now()->endOfDay()->toDateString();
                break;

            case 'yesterday':
                $start = Carbon::yesterday()->startOfDay()->toDateString();
                $end = Carbon::yesterday()->endOfDay()->toDateString();
                break;

            case 'this-month':
                $start = Carbon::now()->startOfMonth()->startOfDay()->toDateString();
                $end = Carbon::now()->endOfDay()->toDateString();
                break;

            case 'last-month':
                $start = Carbon::now()->subMonth()->startOfMonth()->startOfDay()->toDateString();
                $end = Carbon::now()->subMonth()->endOfMonth()->endOfDay()->toDateString();
                break;

            case 'last-2-months':
                $start = Carbon::now()->subMonths(2)->startOfMonth()->startOfDay()->toDateString();
                $end = Carbon::now()->subMonth()->endOfMonth()->endOfDay()->toDateString();
                break;

            case 'last-3-months':
                $start = Carbon::now()->subMonths(3)->startOfMonth()->startOfDay()->toDateString();
                $end = Carbon::now()->subMonth()->endOfMonth()->endOfDay()->toDateString();
                break;

            default: // last-7-days
                $start = Carbon::now()->subWeek()->startOfDay()->toDateString();
                $end = Carbon::yesterday()->endOfDay()->toDateString();
                break;
        }

        return compact('start', 'end');
    }

    /**
     * @param string $start
     * @param string $end
     * @param string $format
     * @return array
     */
    protected function setAggregationDailyDateRanges($start, $end, $format = 'M d, Y')
    {
        $date_ranges = [];

        try {
            $from = Carbon::parse($start)->startOfDay()->timestamp;
            $to = Carbon::parse($end)->endOfDay()->timestamp;

            while ($from <= $to) {
                $date_ranges[] = [
                    'from' => Carbon::createFromTimestamp($from)->format($format),
                    'to' => Carbon::createFromTimestamp($from += 24 * 60 * 60)->format($format)
                ];
            }
        } catch (Exception $e) {
        }

        return $date_ranges;
    }

    /**
     * @return array
     */
    protected function defaultAggregationNames()
    {
        return $this->config['defaults']['aggregation_names'];
    }

    /**
     * @return string
     */
    protected function defaultIndex()
    {
        return $this->config['defaults']['index'];
    }

    /**
     * @return string
     */
    protected function defaultType()
    {
        return $this->config['defaults']['type'];
    }

    /**
     * @param Collection $query
     * @param string $type
     * @return array
     */
    protected function setSearchQueryFilters(Collection $query, $type)
    {
        $filters = [];

        if ($query->isEmpty()) {
            return $filters;
        }

        // get properties
        $properties = $this->getMappingProperties($type);

        // get text type properties
        $text_type_props = $this->getMappingPropertiesByDataType($properties, 'text');

        // get keyword type properties
        // included types: keyword, ip, integer, array
        $keyword_type_props = $this->getMappingPropertiesByDataType($properties, ['keyword', 'ip', 'integer', 'array']);

        // get boolean type properties
        $bool_type_props = $this->getMappingPropertiesByDataType($properties, 'boolean');

        // prepare keyword data type filter
        $term_filter = $this->setBoolQueryClause($query, $keyword_type_props, 'term', 'filter');

        // prepare text data type matching
        $full_text_match = $this->setBoolQueryClause($query, $text_type_props, 'match', 'must');

        // prepare boolean data type filter
        $bool_filter = $this->setBoolQueryClause($query, $bool_type_props, 'term', 'filter', function ($value) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        });

        foreach (compact('term_filter', 'full_text_match', 'bool_filter') as $filter) {
            foreach ($filter as $occur => $context) {
                foreach ($context as $field) {
                    $filters[$occur][] = $field;
                }
            }
        }

        return $filters;
    }

    /**
     * @param Collection $query
     * @param array $properties
     * @param string $context
     * @param string $occur
     * @param callable|null $callback
     * @return array
     */
    protected function setBoolQueryClause(Collection $query, array $properties, $context, $occur, callable $callback = null)
    {
        $data = [];

        $query->only($properties)->each(function ($value, $key) use ($context, $occur, $callback, &$data) {
            $belongs = $occur;

            // all values that starts with exclamation mark (!) is treated as not equal
            if ($value[0] == '!') {
                $belongs = 'must_not';
                $value = ltrim($value, '!');
            }

            $data[$belongs][] = [$context => [$key => is_callable($callback) ? $callback($value) : $value]];
        });

        return $data;
    }

    /**
     * @param Collection $properties
     * @param string|array $data_type
     * @return array
     */
    protected function getMappingPropertiesByDataType(Collection $properties, $data_type)
    {
        $data_types = is_string($data_type) ? [$data_type] : $data_type;

        return $properties->filter(function ($field) use ($data_types) {
            return in_array($field['type'], $data_types);
        })->keys()->all();
    }

    /**
     * @param string $type
     * @return Collection
     */
    protected function getMappingProperties($type)
    {
        return collect($this->config['mappings'][$type]['properties']);
    }

    /**
     * @param string $method
     * @param array|null $args
     * @return mixed
     * @throws \ReflectionException
     */
    public function __call($method, $args)
    {
        if (method_exists($this, $method)) {
            $reflection_method = new \ReflectionMethod($this, $method);

            foreach ($reflection_method->getParameters() as $param) {
                $position = $param->getPosition();

                if (isset($args[$position])) {
                    continue;
                }

                if (!$param->isDefaultValueAvailable()) {
                    switch ($param->name) {
                        case 'type':
                            $arg_value = $this->defaultType();
                            break;

                        case 'index':
                            $arg_value = $this->defaultIndex();
                            break;

                        case 'settings':
                            $arg_value = $this->config['settings'];
                            break;

                        case 'mappings':
                            $arg_value = $this->config['mappings'];
                            break;

                        default:
                            $arg_value = null;
                            break;
                    }
                } else {
                    $arg_value = $param->getDefaultValue();
                }

                $args[$position] = $arg_value;
            }

            return call_user_func_array([$this, $method], $args);
        }
    }
}
