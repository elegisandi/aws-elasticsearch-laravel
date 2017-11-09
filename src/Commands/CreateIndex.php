<?php

namespace elegisandi\AWSElasticsearchService\Commands;

use Illuminate\Console\Command;
use elegisandi\AWSElasticsearchService\ElasticSearchFacade;

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
        if ($this->option('reset')) {
            ElasticSearchFacade::deleteIndex();
        }

        ElasticSearchFacade::createIndex();
    }
}
