<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'codigo' => ['required', 'string', 'max:50'],
            'referencia' => ['required', 'string', 'max:50'],
            'sku' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'stock' => ['required', 'integer', 'min:1'],
            'precio' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'iva' => ['required', 'numeric', 'min:0'],
            'marca_id' => ['required', 'integer', 'exists:marcas,id_marca'],
            'catalogo_id' => ['required', 'integer', 'exists:catalogos,id_catalogo'],
            'imagenes' => ['nullable', 'array', 'min:1'],
            'imagenes.*.id' => ['nullable', 'integer', 'exists:galeria_productos,id_galeria_prod'],
            'imagenes.*.destacada' => ['required', 'integer', 'in:0,1'],
            'imagenes.*.file' => ['nullable', 'file', 'max:2000'],
        ];
    }
}
