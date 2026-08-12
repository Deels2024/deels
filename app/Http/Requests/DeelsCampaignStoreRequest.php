<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Category;

class DeelsCampaignStoreRequest extends CampaignStoreRequest
{
    protected function prepareForValidation(): void
    {
        $category = $this->input('category');
        if ($category !== null && $category !== '') {
            $resolved = null;
            if (is_numeric($category)) {
                $resolved = Category::find((int) $category);
            } else {
                $resolved = Category::query()
                    ->where('slug', (string) $category)
                    ->orWhere('category_name', (string) $category)
                    ->first();
            }

            if ($resolved) {
                $this->merge(['category' => $resolved->id]);
            }
        }

        $this->merge([
            'short_description' => $this->input('short_description', $this->input('description')),
            'start_date' => $this->input('start_date', now()->toDateString()),
            'end_date' => $this->input('end_date', $this->input('ends_at')),
            'skip' => 1,
        ]);

        if ($this->hasFile('media') && !$this->hasFile('mainImg')) {
            $this->files->set('mainImg', $this->file('media'));
        }
    }
}
