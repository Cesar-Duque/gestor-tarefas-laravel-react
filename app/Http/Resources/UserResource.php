<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/*
|##########################################################################
| # TEORIA: API Resources = DTO para camada API                           #
|##########################################################################
|
| DTO = Data Transfer Object = "objeto que só carrega dados"
|
| PROBLEMA se eu retornar Model direto:
|   return User::find(1);
| Resultado:
|   { "id": 1, "name": "João", "email_verified_at": ..., "created_at": ...,
|     "secret_internal_column": ... }
|
| - Não há controle sobre QUAIS campos são expostos (hidden não é tão flexível)
| - Quero formatar a data (d/m/Y em vez de ISO)?
| - Quero INCLUIR campos calculados (ex: "tasks_count" do usuário)?
| - Se mudar o nome da coluna na tabela, QUEBRA TODOS os clientes da API?
|
| SOLUÇÃO: API Resources (Laravel), aka "Transformers" / "ViewModel".
|
| Como usar:
|   return new UserResource($user);   // para 1 item
|   return UserResource::collection($users); // para lista
|
| O método toArray() define EXATAMENTE o que vai para o JSON.
|##########################################################################
*/
class UserResource extends JsonResource
{
    /**
     * Transforme o Model User em um array JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        /*
         * 🔒 Segurança: EXPONHA APENAS O NECESSÁRIO!
         * Nunca envie password, remember_token, etc.
         * O "hidden" do model funciona, mas o Resource dá mais controle.
         */
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,

            // Data formatada para ISO 8601 (padrão internacional JSON)
            // $this->email_verified_at é objeto Carbon (via casts)
            'email_verified_at' => $this->email_verified_at
                ? $this->email_verified_at->toIso8601String()
                : null,

            // Campos calculados de data em formato amigável e formato ISO
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            /*
             * Campos RELACIONADOS (opcional, se carregados via eager load)
             * ->count() é melhor que ->count de collection
             * O método whenLoaded() só inclui se o relacionamento foi
             * carregado (evita lazy loading acidental).
             */
            'categories_count' => $this->whenCounted('categories'),
        ];
    }
}
