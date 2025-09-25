<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['sometimes', 'nullable', 'exists:clientes,id'],
            'fecha' => ['sometimes', 'required', 'date'],
            'estado' => ['sometimes', 'nullable', 'string'],
            'detalles' => ['sometimes', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required_with:detalles', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required_with:detalles', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
