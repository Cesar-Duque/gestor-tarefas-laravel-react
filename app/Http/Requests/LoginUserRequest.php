<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/*
| Esta Form Request valida os campos de LOGIN.
*/
class LoginUserRequest extends FormRequest
{
    /**
     * Qualquer pessoa pode tentar login (rota pública).
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Regras de login:
     *   - email obrigatório e formato válido
     *   - password obrigatório (mínimo 8 chars, vamos ser flexíveis aqui: o
     *     hash de senha é checado pelo Auth::attempt)
     */
    public function rules()
    {
        return [
            'email'    => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * Mensagens de erro personalizadas.
     */
    public function messages()
    {
        return [
            'email.required' => 'Por favor, informe seu e-mail.',
            'password.required' => 'Por favor, informe sua senha.',
        ];
    }
}
