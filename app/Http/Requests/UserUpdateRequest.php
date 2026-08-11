<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserUpdateRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email:rfc,dns',
            'username' => 'required|unique:users,username,' . Auth::user()->id . ',id,deleted_at,NULL',
            'name' => 'required|string|min:2',
            'gender' => 'sometimes',
            'phone' => 'sometimes',
            'address' => 'sometimes',
            'country_id' => 'sometimes',
            'first_message_followings_only' => 'sometimes|boolean',
        ];
    }
}
