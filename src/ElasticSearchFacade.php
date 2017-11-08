<?php

namespace elegisandi\AWSElasticsearchService;

use Illuminate\Support\Facades\Facade;
use Illuminate\Http\Request;

/**
 * Class ElasticSearchFacade
 * @package elegisandi\AWSElasticsearchService
 *
 * @method static array aggregations(array $aggs, $type = null, $index = null)
 * @method static array search(array $query = [], array $options, array $range = [], $type = null, $index = null)
 * @method static array setSearchParams(Request $request, array $defaults = [], $type = null)
 * @method static array getDateRange($range)
 * @method static array setAggregationDailyDateRanges($start, $end)
 * @method static array defaultAggregationNames
 * @method static array getDocument($id, $type = null, $index = null)
 * @method static array getSettings($index = null)
 * @method static array updateSettings(array $settings, $index = null)
 * @method static array getMappings($index = null, $type = null)
 * @method static array updateMappings(array $properties, $type = null, $index = null)
 * @method static array createIndex(array $mappings = null, array $settings = null, $index = null)
 * @method static bool getIndex($index = null)
 * @method static array deleteIndex($index = null)
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
