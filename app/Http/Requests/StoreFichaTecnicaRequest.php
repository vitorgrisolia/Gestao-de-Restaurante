<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFichaTecnicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeAdministrarCadastros() ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['ingredientes' => ['present', 'array'], 'ingredientes.*.ingrediente_id' => ['required', 'distinct', 'exists:ingredientes,id'], 'ingredientes.*.quantidade' => ['required', 'numeric', 'gt:0', 'max:999999']];
    }
}
