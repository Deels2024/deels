<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use Illuminate\Console\Command;

class TestChatGPTModeration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatgpt:test {text=тест}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $text = $this->argument('text');

        $chatgpt = new ChatGPTHelper();
        $telegram = new AppHelper();
        $chatgpt_response = $chatgpt->ping();
        if (!$chatgpt_response) {
            echo "ChatGPT service ping error\n";
            $telegram->telegram_message('ChatGPT service ping error');
        } else {
            echo "ChatGPT service is OK\n";
        }
        $chatgpt_response = $chatgpt->moderation_text($text);
        if (isset($chatgpt_response['flagged'])) {
            echo "Moderation  is OK\n";
            echo json_encode($chatgpt_response, JSON_UNESCAPED_UNICODE)."\n";
        } else {
            echo "ChatGPT service Moderation error\n";
            $telegram->telegram_message('ChatGPT service Moderation error');
        }

    }
}
