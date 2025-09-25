<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'precio_unitario' => ['required', 'numeric', 'min:0'],
            'costo_unitario' => ['required', 'numeric', 'min:0'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'proveedor_id' => ['nullable', 'exists:proveedor,id'],
            'estado' => ['nullable', 'in:activo,inactivo,Activo,Inactivo'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'precio_unitario.required' => 'El precio unitario es obligatorio',
            'costo_unitario.required' => 'El costo unitario es obligatorio',
        ];
    }
}
