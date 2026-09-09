<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/*
|##########################################################################
| # TEORIA: Middleware + Pipeline Pattern (Arquitetura de Cebola)         #
|##########################################################################
|
| O QUE É UM MIDDLEWARE?
|   É uma camada (classe PHP com um método handle()) que fica ENTRE a
|   requisição e o controller, como uma camada de cebola.
|
|   A requisição entra por fora → passa por cada middleware na ORDEM
|   → chega ao controller → a resposta volta PASSANDO pelos mesmos
|   middlewares (agora ao contrário).
|
|      Request  → M1 → M2 → M3 → Controller → M3 → M2 → M1 → Response
|
|   Isso é um "Middleware Pipeline". O Laravel implementa isso usando o
|   padrão Chain of Responsibility + Decorator.
|
| ONDE USAR?
|   • Autenticação (middleware 'auth') — se não logado, retorna 401
|   • Autorização ( 'can' )
|   • Rate limiting ('throttle') — impedir brute-force
|   • CORS — adicionar cabeçalhos Access-Control-*
|   • Logging
|   • Manipular strings (TrimStrings, converter '' para null)
|
| Três tipos existem no Kernel:
|   • $middleware          = globais (rodam SEMPRE em QUALQUER rota)
|   • $middlewareGroups    = grupos ('web' e 'api'), aplicados às rotas
|   • $routeMiddleware     = avulsos, usados individualmente:
|                            ->middleware('auth:sanctum')
|
|##########################################################################
*/
class Kernel extends HttpKernel
{
    /**
     * Middlewares GLOBAIS: executam EM TODA E QUALQUER requisição,
     * independente de ser 'web' ou 'api'.
     *
     * Ordem importa! CORS, por exemplo, tem que rodar ANTES de retornar
     * erro de manutenção (caso contrário o browser bloqueia a resposta
     * por política de CORS mesmo em caso de 503).
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,

        /* 🍎 HandleCors
         * - Adiciona os cabeçalhos Access-Control-Allow-Origin/Methods/Headers
         *   definidos em config/cors.php
         * - Também responde automaticamente a requisições PREFLIGHT OPTIONS
         *   (o navegador envia OPTIONS /api/... ANTES do POST real para checar
         *   se o servidor permite o cross-origin).
         * Sem isso, seu React em localhost:3000 NUNCA conseguiria acessar
         * a API em localhost:8000.
         */
        \Fruitcake\Cors\HandleCors::class,

        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,

        /* TrimStrings: remove espaços em branco dos inputs,
         * ConvertEmptyStringsToNull: transforma '' em NULL,
         *   → muito útil para tratar inputs vazios no banco (ex: descrição '')
         */
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * GRUPOS de Middleware:
     * - grupo 'web' → usado em routes/web.php (sessão, cookies, CSRF)
     * - grupo 'api' → usado em routes/api.php (stateless, tokens)
     *
     * São atribuídos pelo RouteServiceProvider automaticamente conforme
     * o arquivo de rota.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        /* Grupo 'api' — o que usamos no routes/api.php */
        'api' => [
            /* 🔐 Sanctum Stateful (cookies + CSRF para SPAs rodando na mesma
             * entidade de domínio) ou Bearer tokens.
             * Neste projeto usamos Bearer tokens (HasApiTokens) para o
             * React standalone (create-react-app em localhost:3000).
             */
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,

            /* ⚖️ Throttle (Rate Limiting)
             * Previne ataque de força bruta. Em 'api' padrão o usuário
             * só pode fazer 60 requisições por minuto (configurado em
             * RouteServiceProvider). Se ultrapassar: 429 Too Many Requests.
             */
            'throttle:api',

            /* 🔗 Route Model Binding (ex: Task $task injeta automaticamente
             * a tarefa quando você faz GET /api/tasks/{task})
             */
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Middlewares avulsos (podem ser aplicados individualmente em rotas
     * ou controllers via ->middleware('nome') ou no __construct()).
     *
     * Aqui nós usaremos muito o 'auth:sanctum' que pede autenticação.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
