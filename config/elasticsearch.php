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
        'number_of_shards' => 5,
        'number_of_replicas' => 1
    ],
    'default_aggregation_names' => [],
];
