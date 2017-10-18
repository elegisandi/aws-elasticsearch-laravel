<?php

/*
|--------------------------------------------------------------------------
| Elasticsearch Configuration
|--------------------------------------------------------------------------
|
| Set your IAB login credentials here.
|
*/

return [
    'mappings' => [],
    'settings' => [
        'number_of_shards' => 5,
        'number_of_replicas' => 1
    ],
];
