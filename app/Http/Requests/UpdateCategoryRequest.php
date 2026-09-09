<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    /**
     * No UPDATE, a regra "unique" DEVE ignorar o ID atual:
     *   unique:categories,name,{id},id,user_id,$userId
     * Caso contrário, ao editar "Trabalho" sem mudar o nome, daria
     * "já cadastrado" (pois o próprio registro existe).
     *
     * 🎓 Diferença importante:
     *   $this->route('category') pode ser:
     *     • Route Model Binding → objeto Category (use Category $category)
     *     • Sem binding        → ID como string/int (use int $category)
     *   Aqui o controller usa `int $category` param → sem binding.
     *   Então, basta castar para int o ID do parâmetro da URL diretamente.
     */
    public function rules()
    {
        $categoryId = (int) $this->route('category');
        $userId     = $this->user()->id;

        return [
            'name'        => ['required', 'string', 'min:3', 'max:100',
                "unique:categories,name,{$categoryId},id,user_id,{$userId}"
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
