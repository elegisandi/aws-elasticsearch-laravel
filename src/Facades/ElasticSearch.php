<?php

namespace elegisandi\AWSElasticsearchService\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class ElasticSearch
 * @package elegisandi\AWSElasticsearchService\Facades
 *
 * @method static array aggregations(array $aggs, array $query = [], array $options = [], $type = null, $index = null)
 * @method static array search(array $query = [], array $options = [], array $range = [], $type = null, $index = null)
 * @method static array count(array $query = [], array $range = [], $type = null, $index = null)
 * @method static array setSearchParams(Request $request, array $defaults = [], $type = null)
 * @method static array getDateRange($range, $format = null)
 * @method static array setAggregationDailyDateRanges($start, $end, $format = null)
 * @method static array defaultAggregationNames
 * @method static string defaultIndex
 * @method static string defaultType
 * @method static array setSearchQueryFilters(Collection $query, array $bool_clauses = [], $type = null)
 * @method static array setBoolQueryClause(Collection $query, array $properties, $context, $occur, callable $callback = null)
 * @method static array getMappingPropertiesByDataType(Collection $properties, $data_type)
 * @method static Collection getMappingProperties($type = null)
 * @method static array indexDocument(array $body, $type = null, $index = null)
 * @method static array getDocument($id, $type = null, $index = null)
 * @method static array updateDocument(array $fields, $id, $type = null, $index = null)
 * @method static array deleteDocument($id, $type, $index)
 * @method static array getSettings($index = null)
 * @method static array updateSettings(array $settings, $index = null)
 * @method static array getMappings($index = null, $type = null)
 * @method static array updateMappings(array $properties, $type = null, $index = null)
 * @method static array createIndex(array $mappings = null, array $settings = null, $index = null)
 * @method static bool getIndex($index = null)
 * @method static array deleteIndex($index = null)
 */
class ElasticSearch extends Facade
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
