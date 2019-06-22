<?php

namespace App\Console\Commands;

use App\Jobs\TemplateMonsterImportJob;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TemplateMonsterImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'template-monster:import {id*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import template from templatemonster.com';

    /** @var Client */
    private $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = new Client();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $identifiers = $this->argument('id');

        foreach ($identifiers as $identifier) {
            TemplateMonsterImportJob::dispatch($identifier)->onQueue('import');
        }
    }
}
