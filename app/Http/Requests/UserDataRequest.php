<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserDataRequest extends FormRequest
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
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'tipo_documento' => 'required',
            'numero_documento' => 'required',
            'celular' => 'required',
            'codigo' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'nombres' => 'nombre',
            'apellidos' => 'apellido',
            'tipo_documento' => 'tipo de documento',
            'numero_documento' => 'número de documento',
            'celular' => 'número',
            'codigo' => 'código',
        ];
    }

    public function messages()
    {
        return [
            'nombres.required' => 'El :attribute es obligatorio',
            'apellidos.required' => 'El :attribute es obligatorio',
            'tipo_documento.required' => 'El :attribute es obligatorio',
            'numero_documento.required' => 'El :attribute es obligatorio',
            'celular.required' => 'Ingresa un :attribute de contacto',
            'codigo.required' => 'El :attribute es obligatorio',
        ];
    }
}
