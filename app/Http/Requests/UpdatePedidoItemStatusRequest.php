<?php

namespace App\Http\Requests;

use App\StatusItemPedido;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePedidoItemStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->papel->podeOperarProducao() ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(StatusItemPedido::class)->only([
                StatusItemPedido::EmPreparo,
                StatusItemPedido::Pronto,
                StatusItemPedido::Entregue,
            ])],
        ];
    }

    public function status(): StatusItemPedido
    {
        return StatusItemPedido::from((string) $this->validated('status'));
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.required' => 'Informe o próximo status do item.',
            'status.enum' => 'O status informado não pertence ao fluxo de produção.',
        ];
    }
}
