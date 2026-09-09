<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    /*
     * Mesmas regras de StoreTaskRequest, pois os campos são iguais.
     * Em projetos reais você poderia separar DTOs ou usar traits.
     */
    public function rules()
    {
        return [
            'title'       => ['required', 'string', 'min:2', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
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
            'due_date'    => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function messages()
    {
        return [
            'status.in'      => 'Status inválido. Escolha: pendente, em_progresso ou concluida.',
            'prioridade.in'  => 'Prioridade inválida. Escolha: baixa, media ou alta.',
        ];
    }
}
