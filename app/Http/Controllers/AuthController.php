<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/*
|##########################################################################
| # Controller de Autenticação (Auth)                                      #
|##########################################################################
|
| ########################################################################
| # TEORIA: Controller — "O Maestro"                                     #
| ########################################################################
| O Controller NÃO deve ter lógica de negócio.
| Seu papéis são:
|   1) Receber a Request (já validada pelo Form Request)
|   2) Chamar o(s) Service(s) para executar a lógica de negócio
|   3) Montar e retornar a Response adequada (Resource) com status HTTP
|
| É apenas um ORQUESTRADOR.
|
| STATUS HTTP CORRETOS (REST semântico):
|   200 OK           = operação de leitura/edição OK
|   201 Created      = recurso CRIADO (POST)
|   204 No Content   = exclusão OK (sem body)
|   400 Bad Request  = requisição malformada
|   401 Unauthorized = não autenticado (logar primeiro)
|   403 Forbidden    = autenticado, mas não tem PERMISSÃO
|   404 Not Found    = recurso não existe
|   422 Unprocessable = dados inválidos (Form Request retorna automaticamente)
|   500 Internal Server Error = bug no servidor
|##########################################################################
*/
class AuthController extends Controller
{
    /**
     * Injeção de dependência do Service (Constructor Injection).
     *
     * 🎓 TEORIA: Injeção de Dependência + IoC Container do Laravel
     *   Ao invés de criar new AuthService(), pedimos NO CONSTRUTOR.
     *   O Service Container (coração do Laravel) resolve automaticamente,
     *   instancia a classe e injeta.
     *
     *   Vantagens: desacoplamento, fácil de testar (mock), flexível.
     */
    public function __construct(
        protected AuthService $authService,
    ) {}

    /**
     * 📝 REGISTRO (POST /api/register) — ROTA PÚBLICA
     *
     * @return JsonResponse 201 Created com user + Bearer token
     */
    public function register(StoreUserRequest $request): JsonResponse
    {
        /*
         * $request->validated() = retorna os dados JÁ VALIDADOS
         * pelo Form Request. Se algo estiver errado, nem chegou aqui.
         */
        $result = $this->authService->register($request->validated());

        /*
         * 201 Created = REST semântico para criação de recurso.
         * Em headers também poderíamos colocar Location: /api/users/{id}
         */
        return response()->json([
            'message' => 'Usuário cadastrado com sucesso.',
            'user'    => new UserResource($result['user']),
            'token'   => $result['token'],
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * 🔐 LOGIN (POST /api/login) — ROTA PÚBLICA
     *
     * Credenciais corretas → retorna 200 com user + token.
     * Credenciais erradas → retorna 401 Unauthorized.
     */
    public function login(LoginUserRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (! $result) {
            return response()->json([
                'message' => 'E-mail ou senha inválidos.',
                'errors'  => [
                    'email' => ['Credenciais não conferem.'],
                ],
            ], 401);
        }

        return response()->json([
            'message'    => 'Login efetuado com sucesso.',
            'user'       => new UserResource($result['user']),
            'token'      => $result['token'],
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * 👤 DADOS DO USUÁRIO LOGADO (GET /api/user)
     * Rota protegida por auth:sanctum.
     */
    public function me(Request $request): UserResource
    {
        /*
         * $request->user() → usuário autenticado (via Bearer token no header)
         * Retornamos via UserResource (JSON estruturado).
         */
        return new UserResource($request->user());
    }

    /**
     * 🚪 LOGOUT (POST /api/logout)
     * Invalida o token atual (servidor-side).
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logout efetuado com sucesso.',
        ], 200);
    }
}
