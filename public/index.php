<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|##########################################################################
| # TEORIA DO DESENVOLVIMENTO WEB: Front Controller Pattern               #
|##########################################################################
|
| Este é o arquivo PUBLIC/index.php — O ÚNICO arquivo de entrada da sua
| aplicação PHP! TUDO passa por aqui. Isso se chama:
|
|               ➡️  FRONT CONTROLLER PATTERN  ⬅️
|
| Vantagens:
|   • Ponto central = configuração só é feita UMA vez
|   • Todas as requisições passam pelo mesmo pipeline de segurança
|   • Fácil de colocar em manutenção, adicionar CORS, etc.
|
| Quando você acessa http://localhost:8000/api/tasks o .htaccess (ou
| o server do artisan) REESCREVE a URL internamente para:
|   index.php?url=/api/tasks
| Ou seja, todo o resto do PHP roda "por trás" deste arquivo.
|
| O fluxo completo (em ordem) é:
|
|  1. Usuário dispara uma requisição HTTP
|  2. Servidor web (Apache/Nginx/Artisan) recebe e direciona para
|     index.php (este arquivo)
|  3. index.php:
|       a. Carrega o autoload do Composer (carrega todas as classes PHP)
|       b. Cria a instância da aplicação ($app = bootstrap/app.php)
|       c. "Resolve" (cria) o Kernel HTTP via Service Container
|       d. Captura a Request variáveis globais do PHP ($_GET, $_POST, etc.)
|       e. Passa a Request pelo Kernel → Middlewares → Router → Controller
|       f. Retorna um objeto Response
|       g. Response → send() envia cabeçalhos + conteúdo para o cliente
|       h. terminate() faz "limpeza" final (logs, sessões, etc.)
|
|##########################################################################
*/

/*
|--------------------------------------------------------------------------
| Verifica modo manutenção (php artisan down)
|--------------------------------------------------------------------------
| Roda ANTES de carregar todo o framework — assim, mesmo que o sistema
| esteja com algum problema, a página de "em manutenção" aparece.
*/
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| AutoLoad do Composer — PSR-4 Autoloading
|--------------------------------------------------------------------------
|
| ########################################################################
| # TEORIA: PSR-4 e Autoloading de Classes                                #
| ########################################################################
| Antigamente você tinha que escrever "require 'ClasseX.php'" para cada
| arquivo PHP. Hoje, o Composer gera UM arquivo (autoload.php) que
| implementa a PSR-4 (PHP Standard Recommendation #4), que mapeia:
|     "App\Http\Controllers\"  →  "app/Http/Controllers/"
| Quando você usa "use App\Foo\Bar" o autoloader procura e inclui o
| arquivo AUTOMATICAMENTE. Nenhum require manual é mais necessário.
|
| Leia: vendor/composer/autoload_psr4.php para ver o mapeamento completo.
*/
require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Executar a Aplicação
|--------------------------------------------------------------------------
*/

/* 1) Bootstrap da aplicação
 *    Aqui é criado o "Service Container" — o "coração" do Laravel.
 *    É uma caixa de ferramentas (injeção de dependência + IoC Container)
 *    que sabe como criar QUALQUER classe do framework.
 */
$app = require_once __DIR__.'/../bootstrap/app.php';

/* 2) Criar o Kernel HTTP via Service Container (App::make())
 *    Kernel = "core" de processamento de requisições.
 *    Ele contém todos os middlewares globais + os grupos (web/api).
 *    Tudo o que é GLOBAL (CORS, trim strings, etc.) roda aqui.
 */
$kernel = $app->make(Kernel::class);

/* 3) ⚡ O NÚCLEO DO FRAMEWORK ACONTECE AQUI
 *
 *   Request::capture()  →  transforma as variáveis globais do PHP
 *                           ($_GET,$_POST,$_SERVER,$_COOKIE,...)
 *                           em UM objeto Illuminate\Http\Request,
 *                           com métodos maravilhosos (ex: $request->all())
 *
 *   $kernel->handle($request)  →  "anda" pelo pipeline de middlewares
 *                                 (onion layers / arquitetura de cebola)
 *                                 → acha a rota → roda controller
 *                                 → devolve um Response
 *
 *   Response->send()  →  usa funções nativas do PHP:
 *                         header() (status code, Content-Type: application/json)
 *                         echo (corpo da resposta em JSON)
 *                         para enviar tudo ao navegador/cliente.
 */
$response = $kernel->handle(
    $request = Request::capture()
)->send();

/* 4) terminate() — pós-processamento:
 *      - Grava logs pendentes
 *      - Fecha conexões de sessão/cache
 *      - Executa callbacks registrados
 *    A resposta JÁ foi enviada ao cliente; isso é cleanup.
 */
$kernel->terminate($request, $response);
