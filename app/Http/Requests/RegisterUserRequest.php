<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
            'name' => 'required|string|min:3',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:8',
            'age' => 'required|integer',
            'profile_pic' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ];
    }
}
