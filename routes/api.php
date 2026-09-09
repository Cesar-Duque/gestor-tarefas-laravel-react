<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubtaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Rotas da API REST
|--------------------------------------------------------------------------
|
| ########################################################################
| # TEORIA DO DESENVOLVIMENTO WEB: O que são "rotas" e "API REST"?
| ########################################################################
|
| 1) O que é uma ROTA?
|    Uma rota é o MAPEAMENTO entre:
|      • Uma URL acessada pelo cliente (ex: /api/user, /api/tasks)
|      • Um VERBO HTTP (GET, POST, PUT, PATCH, DELETE)
|      • Uma função/método que será executada para produzir a resposta
|
|    É o "primeiro porteiro" da aplicação: a requisição chega,
|    o framework compara a URL + verbo com as rotas cadastradas,
|    e direciona para o código correto.
|
| 2) O que é uma API REST? (Representational State Transfer)
|    É um ESTILO DE ARQUITETURA para construção de APIs baseado em 6 pilares:
|
|      • Cliente-Servidor (separação total entre frontend e backend)
|      • Stateless (cada requisição é INDEPENDENTE — o servidor não "lembra"
|                   da requisição anterior; toda autenticação vai no header)
|      • Cacheable (respostas podem ser cacheadas)
|      • Interface Uniforme (uso consistente de verbos HTTP + URIs)
|      • Sistema em Camadas
|      • Código Sob Demanda (opcional)
|
| 3) VERBOS HTTP e seus significados SEMÂNTICOS no REST:
|      GET    = Ler/recuperar recursos (NUNCA altera dados — é "seguro")
|      POST   = Criar um NOVO recurso (ex: criar tarefa, cadastrar usuário)
|      PUT    = Atualizar um recurso INTEIRO (substitui tudo)
|      PATCH  = Atualização PARCIAL (só 1 campo, ex: status da tarefa)
|      DELETE = Remover um recurso
|
| 4) Prefixo "api/" — este arquivo é automaticamente carregado
|    com o prefixo "/api" no RouteServiceProvider. Toda rota aqui
|    dentro vira: http://localhost:8000/api/<sua-rota>
|
| 5) Middleware 'auth:sanctum' — pede que o usuário esteja autenticado
|    usando Bearer Token (Sanctum token).
|
| Rotas são "carregadas" pelo App\Providers\RouteServiceProvider e
| agrupadas com o middleware group 'api' (veja app/Http/Kernel.php).
|
| ########################################################################
| # TABELA RESUMO DE ENDPOINTS:                                          #
| ########################################################################
| 🔓 PÚBLICAS (sem autenticação):
|   POST   /api/register       → AuthController@register
|   POST   /api/login          → AuthController@login
|
| 🔒 PRIVADAS (auth:sanctum ← Header: "Authorization: Bearer <TOKEN>"):
|   GET    /api/user           → AuthController@me
|   POST   /api/logout         → AuthController@logout
|
|   GET    /api/categories     → CategoryController@index    (listar)
|   POST   /api/categories     → CategoryController@store    (criar)
|   GET    /api/categories/{id} → CategoryController@show   (detalhes)
|   PUT    /api/categories/{id} → CategoryController@update (editar)
|   DELETE /api/categories/{id} → CategoryController@destroy (excluir)
|
|   GET    /api/tasks          → TaskController@index (lista paginada)
|   POST   /api/tasks          → TaskController@store (criar)
|   GET    /api/tasks/{id}     → TaskController@show
|   PUT    /api/tasks/{id}     → TaskController@update
|   DELETE /api/tasks/{id}     → TaskController@destroy
|   PATCH  /api/tasks/{id}/toggle → TaskController@toggleComplete
|
*/

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS (autenticação — qualquer pessoa pode acessar)
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login',    [AuthController::class, 'login'])->name('login');

/*
|--------------------------------------------------------------------------
| ROTAS PROTEGIDAS (usuário precisa estar logado com Bearer Token)
|--------------------------------------------------------------------------
|
| 🎓 TEORIA: MIDDLEWARE GROUP
|   Route::group(['middleware' => 'auth:sanctum'], function () {...})
|   = TODAS as rotas dentro do callback exigem que o usuário esteja
|     autenticado. Se não estiver → retorna 401 Unauthorized JSON.
|
| Header obrigatório para acessar estas rotas:
|   Authorization: Bearer <token_aqui>
|
| Exemplo:  Authorization: Bearer 3|gWZ8xXq...
*/
Route::group(['middleware' => 'auth:sanctum'], function () {

    // 👤 Usuário atual + logout
    Route::get('/user',   [AuthController::class, 'me'])->name('user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
     * 🏷️ CRUD de Categories (resource = cria as 7 rotas REST automaticamente).
     * Porém, usamos only() para pegar só o que usamos (não usamos create/edit
     * que são formulários HTML — na API só precisamos de 5).
     */
    Route::apiResource('categories', CategoryController::class)->only([
        'index',
        'store',
        'show',
        'update',
        'destroy',
    ]);

    /*
     * 📝 CRUD de Tasks (5 rotas resource padrão) + rota extra toggle (concluir).
     * Como a rota toggle é Ação Singular (PATCH), colocamos dentro do grupo.
     */
    Route::apiResource('tasks', TaskController::class)->only([
        'index',
        'store',
        'show',
        'update',
        'destroy',
    ]);
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggleComplete'])
        ->name('tasks.toggle');

    /*
     * 🏁 SUBTAREFAS (Subtasks) — Novas rotas integradas
     * 
     * • POST  /api/tasks/{task}/subtasks     → Cria uma subtarefa para uma task específica (Semântica REST)
     * • PATCH /api/subtasks/{subtask}/toggle → Alterna o status da subtarefa usando a ID direta dela
     * • DELETE /api/subtasks/{subtask}        → Deleta a subtarefa usando a ID direta dela
     */
    Route::post('/tasks/{task}/subtasks', [SubtaskController::class, 'store'])
        ->name('subtasks.store');

    Route::patch('/subtasks/{subtask}/toggle', [SubtaskController::class, 'toggleComplete'])
        ->name('subtasks.toggle');

    Route::delete('/subtasks/{subtask}', [SubtaskController::class, 'destroy'])
        ->name('subtasks.destroy');
});
