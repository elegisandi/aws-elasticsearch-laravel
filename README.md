# aws-elasticsearch-laravel
AWS Elasticsearch Service for Laravel/Lumen

**NOTE:** This package only caters search, aggregation, and indexing transactions. Other than that, you can refer to [elasticsearch's official documentation](https://www.elastic.co/guide/en/elasticsearch/client/php-api/index.html).

## Installation

    composer require elegisandi/aws-elasticsearch-laravel

## Laravel/Lumen Integration

- Add service provider to your `config/app.php` providers

      elegisandi\AWSElasticsearchService\ElasticSearchServiceProvider::class

- Add facade to your `config/app.php` aliases

      elegisandi\AWSElasticsearchService\ElasticSearchFacade::class
      
- Set AWS credentials and Elasticsearch service endpoint in your `.env` file

        AWS_ACCESS_KEY_ID
        AWS_SECRET_ACCESS_KEY
        AWS_REGION
        AWS_ELASTICSEARCH_SERVICE_ENDPOINT
        
    Optional .env variables
    
        AWS_ELASTICSEARCH_SHARDS
        AWS_ELASTICSEARCH_REPLICAS
        AWS_ELASTICSEARCH_INDEX
        AWS_ELASTICSEARCH_TYPE
        
**If you want to configure elasticsearch mappings, settings and/or default type and index, just run:**

    php artisan vendor:publish --provider=elegisandi\\AWSElasticsearchService\\ElasticSearchServiceProvider

**For Lumen:**

- Register service provider to your `bootstrap/app.php`

      $app->register(elegisandi\AWSElasticsearchService\ElasticSearchServiceProvider::class);      

## Basic Usage

    <?php
    
    namespace App;
    
    use elegisandi\AWSElasticsearchService\ElasticSearch;
    
    public function index() {
        $service = new ElasticSearchService;
        
        extract($service->setSearchParams(request()));

        $clicks = [];
        $total = 0;

        if ($hits = $service->search($query, $options, $date_range)) {
            $clicks = $hits['hits']['hits'];
            $total = $hits['hits']['total'];
        }
    }
    
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

## Available Methods

* aggregations(array $aggs, $type, $index)

    > returns `Array`

* search(array $query = [], array $options, array $range = [], $type, $index)

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

* getDocument($id, $type, $index)

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