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
    'mappings' => [],
    'settings' => [
        'number_of_shards' => env('AWS_ELASTICSEARCH_SHARDS', 5),
        'number_of_replicas' => env('AWS_ELASTICSEARCH_REPLICAS', 1),
    ],
    'defaults' => [
    	'index' => env('AWS_ELASTICSEARCH_INDEX'),
    	'type' => env('AWS_ELASTICSEARCH_TYPE'),
    	'aggregation_names' => [],
    ],
];
