# aws-elasticsearch-laravel
AWS Elasticsearch Service for Laravel/Lumen

**NOTE:** This package only caters search, aggregation, and indexing transactions. Other than that, you can refer to [elasticsearch's official documentation](https://www.elastic.co/guide/en/elasticsearch/client/php-api/index.html).

## Installation

    composer require elegisandi/aws-elasticsearch-laravel

## Laravel/Lumen Integration

- Add service provider to your `config/app.php` providers

      elegisandi\AWSElasticsearchService\ElasticSearchServiceProvider::class

- Add facade to your `config/app.php` aliases

      'ElasticSearch' => elegisandi\AWSElasticsearchService\ElasticSearchFacade::class
      
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

* aggregations(array $aggs, $type, $index)

    > returns `Array`

* search(array $query = [], array $options = [], array $range = [], $type, $index)

    > returns `Array`

* count(array $query = [], array $range = [], $type, $index)

    > returns `Array`

* setSearchParams(Request $request, array $defaults = [], $type)

    > returns `Array`

* getDateRange($range)

    > returns `Array`
    
* setAggregationDailyDateRanges($start, $end)

    > returns `Array`

* defaultAggregationNames

    > returns `Array`

* defaultIndex

    > returns `String`

* defaultType

    > returns `String`

* setSearchQueryFilters(Collection $query, $type = null)

    > returns `Array`

* setBoolQueryClause(Collection $query, array $properties, $context, $occur, callable $callback = null)

    > returns `Array`

* getMappingPropertiesByDataType(Collection $properties, $data_type)

    > returns `Array`

* getMappingProperties($type = null)

    > returns `Collection`

* indexDocument(array $body, $type = null, $index = null)

    > returns `Array`

* getDocument($id, $type, $index)

    > returns `Array`

* updateDocument(array $fields, $id, $type = null, $index = null)

    > returns `Array`

* getSettings($index = null)

    > returns `Array`

* updateSettings(array $settings, $index)

    > returns `Array`

* getMappings($index, $type)

    > returns `Array`

* updateMappings(array $properties, $type, $index)

    > returns `Array`

* createIndex(array $mappings, array $settings, $index)

* getIndex($index = null)

    > returns `Boolean`

* deleteIndex($index)

    > returns `Array`

## Contributing

Open an issue first to discuss potential changes/additions.

## License

[MIT](https://github.com/elegisandi/aws-elastic-search-laravel/blob/master/LICENSE)
