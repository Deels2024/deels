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

class CheckVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $model;
    private $media;
    private $type;

    public function __construct($model, $media, $type = 'video')
    {
        $this->model = $model;
        $this->media = $media;
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
        $moderation = $model->moderation ?? [];
        $chatgpt = new ChatGPTHelper();
        $status = true;
        $reason = 'Ошибка модерации ИИ';
        try {
            $response = $chatgpt->moderation_video($model->getFile(true), $model->media);
            if (isset($response['flagged'])) {
                $status = $response['flagged'] ? false : true;
                $reason = $response['reason'];
                $model->moderation = $moderation;
                $model->moderated = $status;
                $model->save();
            }
        } catch (\Throwable $e) {
            Log::info('CheckVideo error at line '.$e->getLine());
            Log::info($e->getMessage());
        }

        Log::info(['CheckVideo '.$model->id, $className, $response]);

        $moderation[$this->type]['status'] = $status;
        $moderation[$this->type]['reason'] = $reason;
        $moderation[$this->type]['checked'] = true;
        $model->moderation = $moderation;
        $model->save();

    }
}
