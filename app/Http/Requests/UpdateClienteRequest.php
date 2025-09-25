<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'telefono' => ['sometimes', 'nullable', 'string', 'max:20'],
            'direccion' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
