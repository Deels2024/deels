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

class CheckText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $model;
    private $text;
    private $type;

    public function __construct($model, $text, $type = 'text')
    {
        $this->model = $model;
        $this->text = $text;
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
        $moderation = $model->moderation ?? [];
        if($renew_model) {
            $model = $renew_model;
        }
        $chatgpt = new ChatGPTHelper();
        $status = true;
        $reason = 'Ошибка модерации ИИ';
        try {
            $response = $chatgpt->moderation_text($this->text);
            if (isset($response['flagged'])) {
                $status = $response['flagged'] ? false : true;
                $reason = $response['reason'];
                $model->moderation = $moderation;
                $model->save();
            }
        } catch (\Throwable $e) {
            Log::info('CheckText error at line '.$e->getLine());
            Log::info($e->getMessage());
        }

        $moderation[$this->type]['status'] = $status;
        $moderation[$this->type]['reason'] = $reason;
        $moderation[$this->type]['checked'] = true;
        $model->moderation = $moderation;
        $model->save();

    }
}
