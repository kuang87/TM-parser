<?php

namespace App\Jobs;

use App\Author;
use App\Screenshot;
use App\Template;
use App\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class TemplateMonsterSaveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vendor = Vendor::find('ee968837-6b3d-4ab4-a1ce-89faccf9f0a3');

        DB::transaction(function () use ($vendor){
            $author = Author::where('vendor_id', $vendor->id)
                ->where('author_id', $this->data['author']['author_id'])
                ->first();

            if ($author == null){
                $author = new Author();
                $author->id = Uuid::uuid4()->toString();
                $author->vendor_id = $vendor->id;
                $author->author_id = $this->data['author']['author_id'];
                $author->author_nick = $this->data['author']['author_nick'];
                $author->save();
            }

            $template = Template::where('vendor_id', $vendor->id)
                ->where('template_id', $this->data['id'])
                ->first();

            if ($template == null){
                $template = new Template();
                $template->id = Uuid::uuid4()->toString();
                $template->vendor_id = $vendor->id;
                $template->author_id = $author->id;
                $template->template_id = $this->data['id'];
                $template->state = $this->data['state'];
                $template->price = $this->data['price'];
                $template->downloads = $this->data['downloads'];
                $template->preview = 'http://view/test';
                $template->save();

                foreach ($this->data['screenshots_list'] as $key => $screenshot_val){
                    $screenshot = new Screenshot();
                    $screenshot->id = Uuid::uuid4()->toString();
                    $screenshot->template_id = $template->id;
                    $screenshot->uri = $screenshot_val['uri'];
                    $screenshot->save();
                }
            }
            else{
                $template->state = $this->data['state'];
                $template->price = $this->data['price'];
                $template->downloads = $this->data['downloads'];
                $template->save();
            }
        });
    }
}
