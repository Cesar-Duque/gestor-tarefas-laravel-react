<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | ########################################################################
    | # TEORIA DO DESENVOLVIMENTO WEB: Same-Origin Policy & CORS              #
    | ########################################################################
    |
    | Por QUESTÕES DE SEGURANÇA, os navegadores implementam a
    | Same-Origin Policy (SOP), que por padrão BLOQUEIA requisições
    | JavaScript (fetch/axios XMLHttpRequest) feitas entre origens
    | DIFERENTES.
    |
    | O QUE É A "ORIGEM"?
    |   Origem = protocolo + host + porta
    |   Ex: http://localhost:3000  (React)
    |       http://localhost:8000  (Laravel)
    |       ⚠️ PORTAS DIFERENTES = ORIGENS DIFERENTES! ⚠️
    |   Então o React NÃO consegue fazer fetch ao Laravel POR PADRÃO.
    |
    | COMO RESOLVER? CORS! (Cross-Origin Resource Sharing)
    |   → CORS NÃO É UM "ERRO" → É UM MECANISMO DE SEGURANÇA ←
    |
    | Funciona assim:
    |   1) Navegador envia a requisição ao backend
    |   2) Backend (este arquivo) responde com HEADERS ESPECIAIS HTTP
    |      chamados de "CORS headers":
    |        • Access-Control-Allow-Origin  = quem pode acessar?
    |        • Access-Control-Allow-Methods = quais métodos?
    |        • Access-Control-Allow-Headers = quais cabeçalhos?
    |        • Access-Control-Allow-Credentials = permite cookies/auth?
    |   3) Navegador COMPARA headers permitidos x requisição que foi feita
    |        → se compatível ✅ deixa passar
    |        → se incompatível ❌ BLOQUEIA no lado do cliente (CORS error)
    |
    | ⚠️ IMPORTANTE: O bloqueio acontece NO NAVEGADOR, não no servidor.
    | Postman / curl / insomina NÃO aplicam Same-Origin Policy.
    | Por isso a API funciona no Postman mas NÃO no React.
    |
    | 🔥 PREFLIGHT OPTIONS REQUEST:
    | Para métodos que "não são simples" (ex: POST com JSON, PUT, DELETE,
    | ou header Authorization: Bearer ...) o navegador envia PRIMEIRO
    | uma requisição OPTIONS /api/... para "checar" as permissões.
    | Se a resposta de OPTIONS estiver ok, ele envia a requisição REAL.
    | Esse middleware Fruitcake\Cors\HandleCors responde ao OPTIONS.
    |
    | Ref: https://developer.mozilla.org/pt-BR/docs/Web/HTTP/CORS
    |
    */

    /* Quais caminhos (URLs) devem aceitar cross-origin?
     * Todas as rotas /api/* + /sanctum/csrf-cookie (para SPA stateful).
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /* Quais MÉTODOS HTTP são permitidos?
     * '*' = aceita todos (GET, POST, PUT, PATCH, DELETE, OPTIONS).
     * Poderia ser ['GET', 'POST', 'PUT', 'DELETE'] para ser mais restrito.
     */
    'allowed_methods' => ['*'],

    /* ⚠️ QUAIS ORIGENS PODEM ACESSAR?
     * NÃO DEIXE COMO '*' EM PRODUÇÃO. Isso abre a API para QUALQUER
     * site do mundo fazer requisições autenticadas.
     * Aqui só permitimos o React em localhost:3000 (ambiente dev).
     * Em produção: 'https://seu-app.com' .
     */
    'allowed_origins' => ['http://localhost:3000'],

    'allowed_origins_patterns' => [],

    /* Cabeçalhos permitidos. Accept, Content-Type, Authorization, etc.
     * '*' deixa passar todos.
     */
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    /* Tempo em segundos para o navegador CACHEAR a resposta de OPTIONS.
     * 0 = sem cache. Aumentar em produção.
     */
    'max_age' => 0,

    /* 🔐 Permite enviar CREDENCIAIS (cookies, cabeçalho Authorization)
     * entre origens diferentes.
     * OBRIGATÓRIO = true quando precisamos de autenticação Bearer
     * (o header Authorization cruza as origens).
     * Obs: quando supports_credentials = true, allowed_origins NÃO PODE
     * ser '*' (exigência da especificação CORS).
     */
    'supports_credentials' => true,

];
