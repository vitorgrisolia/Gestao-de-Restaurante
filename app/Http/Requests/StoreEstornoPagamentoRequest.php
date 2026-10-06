<?php

namespace App\Http\Requests;

use App\PapelUsuario;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEstornoPagamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->papel, [PapelUsuario::Proprietario, PapelUsuario::Gerente], true);
    }

    /** @return array<string,ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return ['motivo' => ['required', 'string', 'min:5', 'max:500']];
    }
}
