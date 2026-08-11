<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class ChatGPTHelper
{

    public function ping()
    {
        $website = env('CHAT_GPT_SERVICE_URL');
        $ch = curl_init($website . '/ping');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    public function motivation($username, $moneybox = null)
    {
        $website = env('CHAT_GPT_SERVICE_URL');
        $params = [
            'username' => $username,
        ];
        $headers = array(
            "X-API-KEY: " . env('CHAT_GPT_SERVICE_API_KEY'),
            "Content-type: application/json",
        );
        if ($moneybox) {
            if (is_array($moneybox)) {
                $params['moneybox'] = $moneybox;
            } else {
                $params['moneybox'] = [$moneybox];
            }
        }
        $ch = curl_init($website . '/generate/motivation-text');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true)['motivationText'];
    }

    public function moneybox($category, $name = null, $description = null)
    {
        $website = env('CHAT_GPT_SERVICE_URL');
        $params = [
            'category' => $category,
        ];
        if ($name) {
            $params['name'] = $name;
        }
        if ($description) {
            $params['description'] = $description;
        }

        try {
            $headers = array(
                "X-API-KEY: " . env('CHAT_GPT_SERVICE_API_KEY'),
                "Content-type: application/json",
            );
            $ch = curl_init($website . '/generate/moneybox-data');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 900);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);
//            Log::info($result);
            return json_decode($result, true);
        } catch (\Throwable $e) {
            Log::info($e->getMessage());
        }
    }

    public function copystories($description)
    {
        $website = env('CHAT_GPT_SERVICE_URL');
        $params = [
            'description' => $description,
        ];
        $headers = array(
            "X-API-KEY: " . env('CHAT_GPT_SERVICE_API_KEY'),
            "Content-type: application/json",
        );
        $ch = curl_init($website . '/generate/stories-data');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    public function stories_data_by_video($media)
    {
        $video = file_get_contents(public_path($media->folder . $media->slug_ext));
        $cfile = new \CURLFile(public_path($media->folder . $media->slug_ext), $media->mime_type, $media->slug_ext);
        $website = env('CHAT_GPT_SERVICE_URL');
        $params = [
            'file' => $cfile
        ];
        $headers = array(
            "X-API-KEY: " . env('CHAT_GPT_SERVICE_API_KEY'),
            "Content-type: multipart/form-data",
        );
        $ch = curl_init($website . '/generate/stories-data-by-video');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        curl_close($ch);


        if (isset($error_msg)) {
//
        }

        return json_decode($result, true);
    }

    public function thanks_text($data)
    {
        $website = env('CHAT_GPT_SERVICE_URL');
        $params = $data;
        $headers = array(
            "X-API-KEY: " . env('CHAT_GPT_SERVICE_API_KEY'),
            "Content-type: application/json",
        );
        $ch = curl_init($website . '/generate/thank-you-letter/text');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    public function thanks_image()
    {
        $website = env('CHAT_GPT_SERVICE_URL');
        $headers = array(
            "X-API-KEY: " . env('CHAT_GPT_SERVICE_API_KEY'),
            "Content-type: application/json",
        );
        $ch = curl_init($website . '/generate/thank-you-letter/image');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    public function moderation_text($text) {
        $website = env('CHAT_GPT_SERVICE_URL');
        $params = [
            'text' => $text,
        ];
        $headers = array(
            "X-API-KEY: " . env('CHAT_GPT_SERVICE_API_KEY'),
            "Content-type: application/json",
        );

        $ch = curl_init($website . '/moderation/text');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            Log::info('/moderation/text error: '.$error_msg);
        }
        curl_close($ch);

        return json_decode($result, true);
    }

    public function moderation_image($image) {
//        $data_file = file_get_contents(public_path($image));
        $mime_type = get_image_mime_type($image);
        $cfile = new \CURLFile(public_path($image), $mime_type, 'filename.jpg');
        $website = env('CHAT_GPT_SERVICE_URL');
        $params = [
            'file' => $cfile
        ];
        $headers = array(
            "X-API-KEY: " . env('CHAT_GPT_SERVICE_API_KEY'),
            "Content-type: multipart/form-data",
        );
        $ch = curl_init($website . '/moderation/image');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            Log::info('/moderation/image error: '.$error_msg);
        }
        curl_close($ch);


        if (isset($error_msg)) {
//
        }

        return json_decode($result, true);
    }

    public function moderation_video($path, $media) {
        try {
            $cfile = new \CURLFile(public_path($path), $media->mime_type, $media->slug_ext);
            $website = env('CHAT_GPT_SERVICE_URL');
            $params = [
                'file' => $cfile
            ];
            $headers = array(
                "X-API-KEY: " . env('CHAT_GPT_SERVICE_API_KEY'),
                "Content-type: multipart/form-data",
            );
            $ch = curl_init($website . '/moderation/video');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 900);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                Log::info('/moderation/video error: '.$error_msg);
                Log::info($result);
            }
            curl_close($ch);


            if (isset($error_msg)) {

            }

            return json_decode($result, true);
        } catch (\Throwable $e) {
            Log::info($e->getMessage());
            return null;
        }


    }

    public function assistant_text($text) {
        $website = env('CHAT_GPT_SERVICE_URL');
        $params = [
            'text_prompt' => $text,
            'with_ai_speach' => false,
        ];
        $headers = array(
            "X-API-KEY: " . env('CHAT_GPT_SERVICE_API_KEY'),
        );
        $queryString = http_build_query($params);

        $ch = curl_init($website . '/assistant/v1/text?' . $queryString);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            Log::info('/assistant/text error: '.$error_msg);
        }
        curl_close($ch);

        return json_decode($result, true);
    }




}
