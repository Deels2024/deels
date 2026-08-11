<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ValidCodeRule;
use Illuminate\Foundation\Http\FormRequest;

class CampaignStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "category"    => "required|exists:categories,id",
            "title"       => "required",
            "description" => "required",
            "goal"        => "required|min:500",
//            "code"        => ["required", new ValidCodeRule()],
            "mainImg"     => "required|max:10000",
        ];
    }
}
