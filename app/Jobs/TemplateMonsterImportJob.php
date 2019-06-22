<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TemplateMonsterImportJob extends Command implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $template_id;
    private $client;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $template_id)
    {
        parent::__construct();

        $this->template_id = $template_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->client = new Client();

        echo ('Старт импорта:');

            echo (PHP_EOL . 'Импорт шаблона ' . $this->template_id);
            try{
                $response = $this->client->request('GET', 'http://api2.templatemonster.com:80/v2/templates.item.json', [
                    'query' => [
                        'user' => env('TEMPLATE_MONSTER_USER'),
                        'token' => env('TEMPLATE_MONSTER_TOKEN'),
                        'item_id' => $this->template_id,
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $data = $data['result'][0];

                TemplateMonsterSaveJob::dispatch($data)->onQueue('save');

                sleep(1);

                echo (PHP_EOL . 'Шаблон № ' . $this->template_id . " импортирован успешно");
            }catch (\GuzzleHttp\Exception\GuzzleException $exception){
                $this->error("Error loading!\n");
                $this->info($exception->getMessage());
            }
        echo PHP_EOL;
    }
}
