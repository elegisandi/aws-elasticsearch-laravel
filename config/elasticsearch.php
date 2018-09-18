<?php

/*
|--------------------------------------------------------------------------
| Elasticsearch Configuration
|--------------------------------------------------------------------------
|
| Set your elasticsearch-related configuration here
|
*/

return [
    'aws' => env('AWS_ELASTICSEARCH_SERVICE', false),
    'host' => env('ELASTICSEARCH_ENDPOINT', 'http://localhost') . ':' . env('ELASTICSEARCH_PORT', 9200),
    'mappings' => [],
    'settings' => [
        'number_of_shards' => env('ELASTICSEARCH_SHARDS', 5),
        'number_of_replicas' => env('ELASTICSEARCH_REPLICAS', 1),
    ],
    'defaults' => [
        'index' => env('ELASTICSEARCH_DEFAULT_INDEX'),
        'type' => env('ELASTICSEARCH_DEFAULT_TYPE'),
        'time_filter_field' => env('ELASTICSEARCH_DEFAULT_TIME_FILTER_FIELD'),
        'aggregation_names' => [],
    ],
];
