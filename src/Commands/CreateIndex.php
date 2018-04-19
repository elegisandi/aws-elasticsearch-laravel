<?php

namespace elegisandi\AWSElasticsearchService\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Exception;

/**
 * Class CreateIndex
 * @package elegisandi\AWSElasticsearchService\Commands
 */
class CreateIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:create-index {--reset : If true, will delete the existing index first.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new or reset elasticsearch index.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->laravel instanceof LaravelApplication) {
            try {
                $action = 'created';

                if ($reset = $this->option('reset')) {
                    \elegisandi\AWSElasticsearchService\ElasticSearchFacade::deleteIndex();

                    $action = 'reset';
                }

                \elegisandi\AWSElasticsearchService\ElasticSearchFacade::createIndex();

                $this->info('Elasticsearch index has been successfully ' . $action . '.');
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        } elseif ($this->laravel instanceof LumenApplication) {
            try {
                $action = 'created';

                if ($this->option('reset')) {
                    app('elasticsearch')->deleteIndex();

                    $action = 'reset';
                }

                app('elasticsearch')->createIndex();

                $this->info('Elasticsearch index has been successfully ' . $action . '.');
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }
}
