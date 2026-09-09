<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    /**
     * Regras de criação de tarefa:
     *   • title: obrigatório, até 150 chars
     *   • category_id: opcional, MAS se existir TEM QUE EXISTIR NA TABELA
     *     categories E PERTENCER AO USUÁRIO LOGADO (segurança!)
     *     → usamos cláusura (Closure) para validar ownership
     *   • status: apenas valores ENUM permitidos
     *   • prioridade: apenas ENUM permitidos
     *   • due_date: opcional, se enviado tem que ser data válida >= hoje
     */
    public function rules()
    {
        return [
            'title'       => ['required', 'string', 'min:2', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            /*
             * Closure custom: validar que a categoria pertence ao user logado
             * Não basta só existir em categories.id — tem que ser do usuário!
             */
            'category_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\Category::where('id', $value)
                        ->where('user_id', $this->user()->id)
                        ->exists();
                    if (!$exists) {
                        $fail('A categoria selecionada não existe ou não pertence a você.');
                    }
                }
            ],
            'status'      => ['required', 'in:pendente,em_progresso,concluida'],
            'prioridade'  => ['required', 'in:baixa,media,alta'],
            'due_date'    => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }

    public function messages()
    {
        return [
            'due_date.after_or_equal' => 'A data de vencimento não pode ser no passado.',
            'status.in' => 'Status inválido. Escolha: pendente, em_progresso ou concluida.',
            'prioridade.in' => 'Prioridade inválida. Escolha: baixa, media ou alta.',
        ];
    }
}
