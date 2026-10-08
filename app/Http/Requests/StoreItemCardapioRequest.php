<?php

namespace App\Http\Requests;

use App\TipoVenda;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreItemCardapioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->papel->podeAdministrarCadastros() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'categoria_cardapio_id' => ['required', 'integer', 'exists:categorias_cardapio,id'],
            'setor_producao_id' => ['required', 'integer', 'exists:setores_producao,id'],
            'nome' => [
                'required',
                'string',
                'max:120',
                $this->nomeUnicoNaCategoria(),
            ],
            'descricao' => ['nullable', 'string', 'max:500'],
            'preco' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
            'imagem' => ['nullable', 'url:http,https', 'max:2048'],
            'disponivel' => ['required', 'boolean'],
            'ordem' => ['required', 'integer', 'min:0', 'max:999'],
            'tipo_venda' => ['sometimes', 'required', Rule::enum(TipoVenda::class)],
            'permite_excesso_carne' => ['sometimes', 'required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'categoria_cardapio_id.required' => 'Selecione a categoria.',
            'categoria_cardapio_id.exists' => 'Selecione uma categoria válida.',
            'setor_producao_id.required' => 'Selecione o setor responsável pelo preparo.',
            'setor_producao_id.exists' => 'Selecione um setor de produção válido.',
            'nome.required' => 'Informe o nome do item.',
            'nome.unique' => 'Já existe um item com este nome na categoria.',
            'preco.required' => 'Informe o preço do item.',
            'preco.numeric' => 'Informe um preço válido.',
            'preco.decimal' => 'Use no máximo duas casas decimais no preço.',
            'imagem.url' => 'Informe uma URL de imagem válida.',
            'disponivel.required' => 'Informe a disponibilidade do item.',
            'ordem.required' => 'Informe a ordem do item.',
        ];
    }

    private function nomeUnicoNaCategoria(): Unique
    {
        return Rule::unique('itens_cardapio', 'nome')
            ->where('categoria_cardapio_id', $this->integer('categoria_cardapio_id'));
    }
}
