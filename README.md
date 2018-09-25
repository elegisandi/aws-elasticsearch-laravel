# aws-elasticsearch-laravel
AWS Elasticsearch Service for Laravel/Lumen

**NOTE:** This package only caters search, aggregation, and indexing transactions. Other than that, you can refer to [elasticsearch's official documentation](https://www.elastic.co/guide/en/elasticsearch/client/php-api/index.html).

## Installation

    composer require elegisandi/aws-elasticsearch-laravel

## Laravel/Lumen Integration

- Add service provider to your `config/app.php` providers

      elegisandi\AWSElasticsearchService\ElasticSearchServiceProvider::class

- Add facade to your `config/app.php` aliases

      'ElasticSearch' => elegisandi\AWSElasticsearchService\Facades\ElasticSearch::class
      
- Set AWS credentials and Elasticsearch config in your `.env` file

        AWS_ACCESS_KEY_ID
        AWS_SECRET_ACCESS_KEY
        AWS_REGION

        ELASTICSEARCH_ENDPOINT
        ELASTICSEARCH_PORT
        ELASTICSEARCH_SHARDS
        ELASTICSEARCH_REPLICAS
        ELASTICSEARCH_DEFAULT_INDEX
        ELASTICSEARCH_DEFAULT_TYPE
        ELASTICSEARCH_DEFAULT_TIME_FILTER_FIELD

    When you are already using aws elasticsearch service, set

        AWS_ELASTICSEARCH_SERVICE=true
        
**If you want to configure elasticsearch mappings, settings and/or default type and index, just run:**

    php artisan vendor:publish --provider=elegisandi\\AWSElasticsearchService\\ElasticSearchServiceProvider

**For Lumen:**

- Register service provider to your `bootstrap/app.php`

      $app->register(elegisandi\AWSElasticsearchService\ElasticSearchServiceProvider::class);      

## Basic Usage
    
**Using Facade:**

    <?php
        
    namespace App;
    
    use ElasticSearch;
    
    public function index() {
        extract(ElasticSearch::setSearchParams(request()));

        $clicks = [];
        $total = 0;

        if ($hits = ElasticSearch::search($query, $options, $date_range)) {
            $clicks = $hits['hits']['hits'];
            $total = $hits['hits']['total'];
        }
    }
    
**For Lumen:**

    <?php
            
    namespace App;
    
    public function index() {
        extract(app('elasticsearch')->setSearchParams(request()));

        $clicks = [];
        $total = 0;

        if ($hits = app('elasticsearch')->search($query, $options, $date_range)) {
            $clicks = $hits['hits']['hits'];
            $total = $hits['hits']['total'];
        }
    }

## Console Commands

* Create Index _(creates the default index)_

    `php artisan elasticsearch:create-index`

    To reset existing index,

    `php artisan elasticsearch:create-index --reset`


## Available Methods

* ##### aggregations(`array $aggs`, `array $query = []`, `array $options = []`, `$type`, `$index`)

    > **$aggs** : must follow the structure specified in [elasticsearch docs](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations.html).

    > **$query** : see **`search`** method **`$query`** argument

    > **$options** : see **`search`** method **`$options`** argument

    > returns `Array`

* ##### search(`array $query = []`, `array $options = []`, `array $range = []`, `$type`, `$index`)

    > **$query** : an array of key-value pair of any available properties

    > **$options** : an array of key-value pair of the ff: `from`, `size`, `sort`

    > **$range** : an array representation of [range query](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html).

    > returns `Array`

* ##### count(`array $query = []`, `array $range = []`, `$type`, `$index`)

    > a _(syntactic sugar)_ method of search with zero hits result

    > returns `Int`

* ##### setSearchParams(`Request $request`, `array $defaults = []`, `$type`)

    > an optional and conventional approach of setting search params via query string

    > **$request** : an instance of `\Illuminate\Http\Request`, query variables in used:

    - `range`, see [getDateRange](https://github.com/elegisandi/aws-elasticsearch-laravel#getDateRange-range-format-null) method
    - `start`, a valid date string
    - `end`, a valid date string
    - `sort`, a mapping property
    - `order`, value is either `desc` or `asc`
    - `size`, total results to return _(max of 10000)_

    > **$defaults** : an array of key-value pair of the ff: `sort, order, size`

    > returns `Array`

* ##### getDateRange(`$range`, `$format = null`)

    > **$range** : predefined date range values: `today, yesterday, last-7-days, this-month, last-month, last-2-months, last-3-months`

    > **$format** must be a valid date format, default is `null` which will return a DateTime instance

    > returns `Array`
    
* ##### setAggregationDailyDateRanges(`$start`, `$end`, `$format = null`)

    > **$format** must be a valid date format, default is `null` which will return a DateTime instance

    > returns `Array`

* ##### defaultAggregationNames

    > returns `Array`

* ##### defaultIndex

    > returns `String`

* ##### defaultType

    > returns `String`
    
* ##### defaultTimeFilterField

    > returns `String`

* ##### setSearchQueryFilters(`Collection $query`, `array $bool_clauses = []`, `$type = null`)

    > returns `Array`

* ##### setBoolQueryClause(`Collection $query`, `array $properties`, `$context`, `$occur`, `callable $callback = null`)

    > returns `Array`

* ##### getMappingPropertiesByDataType(`Collection $properties`, `$data_type`)

    > returns `Array`

* ##### getMappingProperties(`$type = null`)

    > returns `Collection`

* ##### indexDocument(`array $body`, `$type = null`, `$index = null`)

    > returns `Array`

* ##### getDocument(`$id`, `$type`, `$index`)

    > returns `Array`

* ##### updateDocument(`array $fields`, `$id`, `$type = null`, `$index = null`)

    > returns `Array`

* ##### deleteDocument(`$id`, `$type = null`, `$index = null`)

    > returns `Array`

* ##### getSettings(`$index = null`)

    > returns `Array`

* ##### updateSettings(`array $settings`, `$index`)

    > returns `Array`

* ##### getMappings(`$index, $type`)

    > returns `Array`

* ##### updateMappings(`array $properties`, `$type`, `$index`)

    > returns `Array`

* ##### createIndex(`array $mappings`, `array $settings`, `$index`)

* ##### getIndex($index = null)

    > returns `Boolean`

* ##### deleteIndex(`$index`)

    > returns `Array`

### NOTE: All methods of the elasticsearch client are now supported.

## Limitations

- Supported data types in **search** method are:

    - keyword
    - text
    - array
    - integer
    - boolean
    - ip

## Contributing

Open an issue first to discuss potential changes/additions.

## License

[MIT](https://github.com/elegisandi/aws-elastic-search-laravel/blob/master/LICENSE)
