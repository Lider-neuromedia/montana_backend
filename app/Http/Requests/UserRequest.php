<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'name' => 'required|string',
            'email' => 'required|email:rfc,dns',
            'password' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'nombre',
            'email' => 'email',
            'password' => 'contraseña',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El :attribute es obligatorio',
            'email.required' => 'El :attribute es obligatorio',
            'password.required' => 'La :attribute es obligatoria',
        ];
    }
}
