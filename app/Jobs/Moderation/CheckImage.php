<?php

namespace App\Jobs\Moderation;

use App\Helpers\ChatGPTHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $model;
    private $image;
    private $type;

    public function __construct($model, $image, $type = 'image')
    {
        $this->model = $model;
        $this->image = $image;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $model = $this->model;
        $className = class_basename($model);
        $renew_model_name = '\\App\\Models\\'.$className;
        $renew_model = $renew_model_name::find($model->id);
        if($renew_model) {
            $model = $renew_model;
        }
        Log::info('CheckImage '.$className.' '.$model->id);
        $moderation = $model->moderation ?? [];
        $chatgpt = new ChatGPTHelper();
        $status = true;
        $reason = 'Ошибка модерации ИИ';
        try {
            $response = $chatgpt->moderation_image($this->image);
            if (isset($response['flagged'])) {
                $status = $response['flagged'] ? false : true;
                $reason = $response['reason'];
                $model->moderation = $moderation;
                $model->save();
            }
            Log::info(['CheckImage '.$className.' '.$model->id, $response]);
        } catch (\Throwable $e) {
            Log::info('CheckImage error at line '.$e->getLine());
            Log::info(json_encode($e));
        }

        $moderation[$this->type]['status'] = $status;
        $moderation[$this->type]['reason'] = $reason;
        $moderation[$this->type]['checked'] = true;
        $model->moderation = $moderation;
        $model->save();

    }
}
