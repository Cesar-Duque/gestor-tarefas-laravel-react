<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/*
|##########################################################################
| # TEORIA: Service Layer (Camada de Serviço) + SRP (Single Responsibility)#
|##########################################################################
|
| ########################################################################
| # S — SOLID: Single Responsibility Principle                         #
| ########################################################################
|   "Uma classe deve ter UM, e apenas UM, motivo para mudar."
|
| O problema de colocar tudo no Controller:
|   class TaskController {
|     public function store(Request $r) {
|       // 50 linhas de lógica aqui (validar, criar, email, etc.)
|       // Se precisar REUSAR essa lógica em um Command (CLI) ou outra
|       // rota da API → você duplica código ❌
|     }
|   }
|
| SOLUÇÃO → Service Layer:
|   Extraia a LÓGICA DE NEGÓCIO para classes de Serviço (Services).
|   O CONTROLLER fica "gordo" em orquestração, magro em lógica:
|     1. Recebe Request já validada pelo Form Request
|     2. Chama Service::method()
|     3. Retorna Response (através de API Resource)
|
|   Services são REUTILIZÁVEIS em controllers, commands, jobs, etc.
|
| Regra prática: se a lógica for "mais de 5 linhas", crie um Service.
|##########################################################################
|
| Exemplo: registrar usuário tem lógica (hash de senha, criar token, etc.)
| → AuthService faz isso. Controller só orquestra.
*/
class AuthService
{
    /**
     * 📝 Cria um novo usuário (REGISTRO).
     *
     * @param  array{name: string, email: string, password: string}  $validated
     * @return array{user: User, token: string}
     */
    public function register(array $validated): array
    {
        /*
         * 🔒 HASH DE SENHA — NUNCA SALVE SENHA EM TEXTO PURO!
         *   bcrypt = função de hash de senha custosa (custa tempo/CPU)
         *   → faz com que ataque bruteforce fique inviável.
         *   Cada senha vira um hash único de 60 chars com salt interno.
         *   Laravel usa Hash::make() por padrão (usa bcrypt).
         */
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        /*
         * 🎟️ Cria Bearer Token (Personal Access Token) via Sanctum
         * $user->createToken('nome-token') → salva entrada em
         *   personal_access_tokens e retorna NewAccessToken object.
         * plainTextToken = token COMPLETO (id + hash separado por pipe)
         *   só aparece UMA VEZ aqui. Salve no localStorage do usuário.
         *   Ex: "1|abcxyz123..."
         */
        $token = $user->createToken('api-token-' . $user->id)->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * 🔐 Efetua LOGIN (valida credenciais e devolve token).
     *
     * Retorna array com user + token, ou null se credenciais inválidas.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{user: User, token: string}|null
     */
    public function login(array $credentials): ?array
    {
        /*
         * Auth::attempt = pega email + senha, encontra usuário por email,
         * verifica Hash::check(senha, hash_salvo). Se bater, retorna true.
         * Internamente usa password_verify() do PHP.
         */
        if (! Auth::attempt($credentials)) {
            return null;
        }

        $user = Auth::user();
        $token = $user->createToken('api-token-' . $user->id)->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    /**
     * 🚪 LOGOUT (deleta o token atual da tabela personal_access_tokens).
     *
     * Obs: com Sanctum, o token é o objeto "currentAccessToken" da request
     * autenticada. Deletar ele = não serve mais (invalidação server-side).
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
