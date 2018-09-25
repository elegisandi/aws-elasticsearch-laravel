<?php

namespace elegisandi\AWSElasticsearchService\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use elegisandi\AWSElasticsearchService\Facades\ElasticSearch;
use Exception;

/**
 * Class UpdateIndexMapping
 * @package elegisandi\AWSElasticsearchService\Commands
 */
class UpdateIndexMapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:update-index-mapping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update current index mapping.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->laravel instanceof LaravelApplication) {
            $class = ElasticSearch::class;
        } elseif ($this->laravel instanceof LumenApplication) {
            $class = app('elasticsearch');
        }

        if (empty($class)) {
            $this->error('Application not supported.');
        }

        try {
            $index = call_user_func([$class, 'defaultIndex']);
            $type = call_user_func([$class, 'defaultType']);
            $mapping = call_user_func([$class, 'getMapping']);
            $properties = $mapping[$index]['mappings'][$type]['properties'];
            $config_props = call_user_func([$class, 'getMappingProperties'])->toArray();

            $new_props = array_diff_key($config_props, $properties);

            if (empty($new_props)) {
                $this->warn('Nothing to update for the current index mapping.');
            }

            call_user_func([$class, 'updateMapping'], $new_props);

            $this->info('Elasticsearch index mapping has been successfully updated.');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
