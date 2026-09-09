<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/*
|##########################################################################
| # TEORIA: Form Requests = Padrão de Separação da Validação              #
|##########################################################################
|
| POR QUE VALIDAR NO BACKEND?
|   Validação client-side (React) é UX, NÃO é SEGURANÇA.
|   Qualquer pessoa pode usar Postman/curl para enviar requisição direta.
|   Regra de ouro: 👉 NUNCA CONFIE NOS DADOS DO CLIENTE 👈
|   Sempre valide TUDO no backend.
|
| ########################################################################
| # PADRÃO FORMA REQUEST (do Laravel)                                    #
| ########################################################################
| Separa a VALIDAÇÃO do CONTROLLER, cumprindo o "S" do SOLID
| (Single Responsibility Principle):
|   • Classe existe com um único propósito: validar dados de entrada.
|   • Controller fica focado em orquestrar (pegar dados, chamar service,
|     retornar resposta).
|
| COMO FUNCIONA?
|   1) Você type-hinta a classe no método do controller:
|        public function store(StoreUserRequest $request)
|   2) O Laravel automaticamente EXECUTA a validação ANTES do controller.
|   3) SE falhar → retorna 422 Unprocessable Entity com JSON:
|        { "message": "...", "errors": { "email": ["obrigatório", ...] } }
|   4) SE passar → o controller executa normalmente.
|
| Métodos desta classe:
|   • authorize() → Permissões (o usuário pode fazer isso?).
|   • rules()     → Regras de validação.
|
| REGRAS COMUNS:
|   required, string, min:3, max:255, email, unique:users,email,
|   confirmed (verifica campo <campo>_confirmation), in:pendente,concluida,
|   exists:categories,id, date, nullable, integer, boolean, etc.
|##########################################################################
*/
class StoreUserRequest extends FormRequest
{
    /**
     * Autorização: O USUÁRIO PODE FAZER ESSA AÇÃO?
     *
     * Registro de usuário é PÚBLICO (qualquer um pode se cadastrar)
     * → return true = sempre autorizado.
     *
     * Para ações restritas (ex: só admin pode deletar), você colocaria aqui:
     *   return $this->user()->is_admin;
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * 📋 REGRAS DE VALIDAÇÃO para Registro de Usuário
     *
     * 🔎 "Nome do campo" => "regras separadas por pipe |"
     *
     * Detalhes:
     *   • name     : obrigatório, string, entre 2 e 100 caracteres
     *   • email    : obrigatório, formato email, único na tabela users
     *   • password : obrigatório, mínimo 8 caracteres, CONFIRMADO
     *                (campo extra 'password_confirmation' no frontend)
     *
     * Formato JSON de erro (caso falhe):
     *   {
     *     "message": "O campo email é obrigatório.",
     *     "errors": {
     *       "email": ["O campo email é obrigatório.", ...],
     *       "password": ["O campo senha deve ter pelo menos 8 caracteres."]
     *     }
     *   }
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules()
    {
        return [
            'name'     => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email', 'max:150'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ];
    }

    /**
     * 🧾 MENSAGENS PERSONALIZADAS (opcional)
     * Se não definir, usa os padrões de resources/lang/*.php.
     * Sobrescreva se quiser mensagens diferentes.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'email.unique' => 'Este e-mail já está cadastrado em nosso sistema.',
            'password.confirmed' => 'As senhas não coincidem. Digite a mesma senha nos dois campos.',
        ];
    }
}
