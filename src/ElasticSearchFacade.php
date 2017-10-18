<?php

namespace elegisandi\AWSElasticsearchService;

use Illuminate\Support\Facades\Facade;

/**
 * Class ElasticSearchFacade
 * @package elegisandi\AWSElasticsearchService
 *
 * @method static ElasticSearch aggregations($aggs = [], $type = 'click', $index = 'clicktracker')
 * @method static ElasticSearch search(array $query = [], $options = [], $range, $type = 'click', $index = 'clicktracker')
 * @method static ElasticSearch getDocument($id, $type = 'click', $index = 'clicktracker')
 * @method static ElasticSearch getSettings($index)
 * @method static ElasticSearch updateSettings(array $settings, $index = 'clicktracker')
 * @method static ElasticSearch updateMappings(array $properties, $type = 'click', $index = 'clicktracker')
 * @method static ElasticSearch createIndex(array $mappings, array $settings = [], $index = 'clicktracker')
 * @method static ElasticSearch getIndex($index = 'clicktracker')
 * @method static ElasticSearch deleteIndex($index = 'clicktracker')
 */
class ElasticSearchFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'elasticsearch';
    }
}
