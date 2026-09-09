<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Autorização:
     * Apenas usuário autenticado (logado) pode criar categorias.
     * $this->user() = usuário autenticado pelo middleware('auth:sanctum').
     * Se ele existe = true, está autenticado.
     */
    public function authorize()
    {
        return $this->user() !== null;
    }

    /**
     * Regras:
     *   • name: obrigatório, entre 3 e 100 chars
     *           unique_per_user (veja regra custom abaixo via cláusura)
     *   • description: opcional
     */
    public function rules()
    {
        $userId = $this->user()->id;

        return [
            /*
             * 🔎 unique:categories,name,NULL,id,user_id,$userId
             *   Regra de "nome de categoria ÚNICO POR USUÁRIO".
             *   Não proíbe globalmente (outros usuários podem ter "Trabalho").
             *   Sintaxe: unique:tabela,coluna,ignorar_id,id_col,filtro_col,filtro_val
             */
            'name'        => ['required', 'string', 'min:3', 'max:100',
                "unique:categories,name,NULL,id,user_id,{$userId}"
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Você já possui uma categoria com este nome.',
        ];
    }
}
