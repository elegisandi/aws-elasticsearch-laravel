<?php

namespace elegisandi\AWSElasticsearchService\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

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
    protected $description = 'Create new elasticsearch index.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->laravel instanceof LaravelApplication) {
            if ($this->option('reset')) {
                \elegisandi\AWSElasticsearchService\ElasticSearchFacade::deleteIndex();
            }

            \elegisandi\AWSElasticsearchService\ElasticSearchFacade::createIndex();
        } elseif ($this->laravel instanceof LumenApplication) {
            if ($this->option('reset')) {
                app('elasticsearch')->deleteIndex();
            }

            app('elasticsearch')->createIndex();
        }
    }
}
