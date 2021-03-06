<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => ['required'],
            'school_id' => ['required'],
            'phone_number' => ['required', 'numeric', 'unique:users'],
            'first_subject' => ['required'],
            'second_subject' => ['required'],
            'password' => ['required', 'min:8', 'max:30'],
            'conf_password' => ['required', 'min:8', 'max:30'],
            'middle_name' => [],
            'last_name' => ['required'],
        ];
    }
}
