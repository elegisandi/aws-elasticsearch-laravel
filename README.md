# aws-elasticsearch-laravel
AWS Elasticsearch Service for Laravel/Lumen

## Installation

    composer require elegisandi/aws-elasticsearch-laravel

## Laravel/Lumen Integration

- Add service provider to your `config/app.php` providers

      elegisandi\AWSElasticsearchService\ElasticSearchServiceProvider::class

- Add facade to your `config/app.php` aliases

      elegisandi\AWSElasticsearchService\ElasticSearchFacade::class
      
- Set AWS credentials and Elasticsearch service endpoint in your `.env` file

        AWS_ACCESS_KEY_ID=
        AWS_SECRET_ACCESS_KEY=
        AWS_REGION=
        AWS_ELASTICSEARCH_SERVICE_ENDPOINT=
        
**If you want to configure mappings and settings of elasticsearch, just run:**

    php artisan vendor:publish --provider=elegisandi\\AWSElasticsearchService\\ElasticSearchServiceProvider

**For Lumen:**

- Register service provider to your `bootstrap/app.php`

      $app->register(elegisandi\AWSElasticsearchService\ElasticSearchServiceProvider::class);      

## Basic Usage

A helper trait is also included in the package for your convenience.

    <?php
    
    namespace App;
    
    use elegisandi\AWSElasticsearchService\ElasticSearch;
    use elegisandi\AWSElasticsearchService\Traits\ElasticSearchHelper;
    
    public function index() {
        extract($this->setSearchParams(request()));

        $clicks = [];
        $total = 0;

        if ($hits = (new ElasticSearchService)->search($query, $options, $date_range)) {
            $clicks = $hits['hits']['hits'];
            $total = $hits['hits']['total'];
        }
    }
    
**Using Facade:**

    if ($hits = ElasticSearch::search($query, $options, $date_range)) {
        $clicks = $hits['hits']['hits'];
        $total = $hits['hits']['total'];
    }
    
**For Lumen:**
    
    if ($hits = app('elasticsearch')->search($query, $options, $date_range)) {
        $clicks = $hits['hits']['hits'];
        $total = $hits['hits']['total'];
    }

## Useful Methods

- #### aggregations($aggs = [], $type = 'click', $index = 'clicktracker')
- #### search(array $query = [], $options = [], $range, $type = 'click', $index = 'clicktracker')

For full available methods, see file [here](https://github.com/elegisandi/aws-elastic-search-laravel/blob/master/src/ElasticSearch.php).

For trait helper methods, see file [here](https://github.com/elegisandi/aws-elastic-search-laravel/blob/master/src/Traits/ElasticSearchHelper.php).

## Contributing

Open an issue first to discuss potential changes/additions.

## License

[MIT](https://github.com/elegisandi/aws-elastic-search-laravel/blob/master/LICENSE)
