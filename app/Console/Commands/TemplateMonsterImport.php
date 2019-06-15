<?php

namespace App\Console\Commands;

use App\Author;
use App\Screenshot;
use App\Template;
use App\Vendor;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

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
        $this->comment('Старт импорта:');

        $vendor = Vendor::find('ee968837-6b3d-4ab4-a1ce-89faccf9f0a3');

        $bar = $this->output->createProgressBar(count($this->argument('id')));

        $bar->start();

        foreach ($this->argument('id') as $id){
            $this->comment(PHP_EOL . 'Импорт шаблона ' . $id);
            try{
                $response = $this->client->request('GET', 'http://api2.templatemonster.com:80/v2/templates.item.json', [
                    'query' => [
                        'user' => env('TEMPLATE_MONSTER_USER'),
                        'token' => env('TEMPLATE_MONSTER_TOKEN'),
                        'item_id' => $id,
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                $data = $data['result'][0];

                DB::transaction(function () use ($vendor, $data){
                    $author = new Author();
                    $author->id = Uuid::uuid4()->toString();
                    $author->vendor_id = $vendor->id;
                    $author->author_id = $data['author']['author_id'];
                    $author->author_nick = $data['author']['author_nick'];
                    $author->save();

                    $template = new Template();
                    $template->id = Uuid::uuid4()->toString();
                    $template->vendor_id = $vendor->id;
                    $template->author_id = $author->id;
                    $template->template_id = $data['id'];
                    $template->state = $data['state'];
                    $template->price = $data['price'];
                    $template->downloads = $data['downloads'];
                    $template->preview = 'http://view/test';
                    $template->save();

                    foreach ($data['screenshots_list'] as $key => $screenshot_val){
                        $screenshot = new Screenshot();
                        $screenshot->id = Uuid::uuid4()->toString();
                        $screenshot->template_id = $template->id;
                        $screenshot->uri = $screenshot_val['uri'];
                        $screenshot->save();
                    }
                });

                sleep(1);
                $bar->advance();
                $this->comment(PHP_EOL . 'Шаблон № ' . $id . " импортирован");
            }catch (\GuzzleHttp\Exception\GuzzleException $exception){
                $this->error("Error loading!\n");
                $this->info($exception->getMessage());
            }
        }
        $bar->finish();
        echo PHP_EOL;

        //echo "TemplateMonster\n";
       //dd($this->argument('id'));

       //$bar = $this->output->createProgressBar(2);

       //$bar->start();
       //foreach ($this->argument('id') as $argument){
       //    $bar->advance();
       //    sleep(2);
       //}
       //$bar->finish();
       //echo PHP_EOL;
    }
}
