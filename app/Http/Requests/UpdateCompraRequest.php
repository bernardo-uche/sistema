<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => ['sometimes', 'nullable', 'exists:proveedor,id'],
            'fecha' => ['sometimes', 'required', 'date'],
            'estado' => ['sometimes', 'nullable', 'string'],
            'detalles' => ['sometimes', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required_with:detalles', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required_with:detalles', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required_with:detalles', 'numeric', 'min:0'],
        ];
    }
}
