<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'stock' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'precio_unitario' => ['sometimes', 'required', 'numeric', 'min:0'],
            'costo_unitario' => ['sometimes', 'required', 'numeric', 'min:0'],
            'fecha_vencimiento' => ['sometimes', 'nullable', 'date'],
            'categoria_id' => ['sometimes', 'nullable', 'exists:categorias,id'],
            'proveedor_id' => ['sometimes', 'nullable', 'exists:proveedor,id'],
            'estado' => ['sometimes', 'nullable', 'in:activo,inactivo,Activo,Inactivo'],
        ];
    }
}
