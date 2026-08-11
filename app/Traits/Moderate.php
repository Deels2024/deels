<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Moderate
{
    public function isModerated()
    {
        $checked = 0;
        $status = 0;
        $model_valid = false;
        $model_checked = false;
        $moderation = $this->moderation ?? [];
        foreach ($moderation as $data) {
            if (isset($data['status']) && isset($data['checked'])) {
                if ($data['checked']) {
                    $checked++;
                }
                if ($data['status']) {
                    $status++;
                }
            }
        }

        if ($status >= 3) {
            $model_valid = true;
        }
        if ($checked >= 3) {
            $model_checked = true;
        }
        return [
            'valid' => $model_valid,
            'checked' => $model_checked,
        ];

    }

    public function getReasons() {
        $reasons = null;
        if (($this->status ?? null) === 1 || (!$this->declined ?? false)) {
            return null;
        }
        $reasons_array = [];
        $items = [
            'text' => 'Причина: Текст ',
            'image' => 'Причина: Изображение',
            'video' => 'Причина: Видео',
        ];
        if($this->moderation) {
            foreach($this->moderation as $key => $moderation) {
                if(isset($moderation['reason']) && $moderation['reason']) {
                    if(!in_array($moderation['reason'], $reasons_array)) {
                        $reasons_array[] = $items[$key].' '.Str::lower($moderation['reason']);
                    }
                }
            }
            if(!empty($reasons_array)) {
                $reasons = implode('<br>', $reasons_array);
            }
        }
        return $reasons;
    }

    public function set_moderated($type, $status = true) {
        $moderation = $this->moderation ?? [];
        $moderation[$type]['status'] = $status;
        $moderation[$type]['checked'] = $status;
        $this->moderation = $moderation;
        $this->saveQuietly();
    }

    public function set_all_moderated($status = true) {
        $types = ['text', 'image', 'video'];
        $moderation = $this->moderation ?? [];
        foreach ($types as $type) {
            $moderation[$type]['status'] = $status;
            $moderation[$type]['checked'] = $status;
        }
        $this->moderation = $moderation;
        $this->ai_moderated = false;
        $this->saveQuietly();
    }
}
