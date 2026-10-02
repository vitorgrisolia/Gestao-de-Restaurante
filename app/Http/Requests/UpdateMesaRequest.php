<?php

namespace App\Http\Requests;

use App\EstadoMesa;
use App\Models\Mesa;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMesaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $mesa = $this->route('mesa');

        return $mesa instanceof Mesa
            && ($this->user()?->can('update', $mesa) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'setor_salao_id' => ['required', 'integer', 'exists:setores_salao,id'],
            'numero' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mesas', 'numero')
                    ->where('setor_salao_id', $this->integer('setor_salao_id'))
                    ->ignore($this->route('mesa')),
            ],
            'capacidade' => ['required', 'integer', 'min:1', 'max:99'],
            'estado' => ['required', Rule::enum(EstadoMesa::class)],
            'ativa' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'setor_salao_id.required' => 'Selecione o setor da mesa.',
            'numero.required' => 'Informe o número ou identificação da mesa.',
            'numero.unique' => 'Já existe uma mesa com esta identificação no setor.',
            'capacidade.required' => 'Informe a capacidade da mesa.',
        ];
    }
}
