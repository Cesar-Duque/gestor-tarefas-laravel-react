<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/*
|##########################################################################
| # TEORIA: Eloquent ORM — Active Record Pattern                         #
|##########################################################################
|
| O QUE É UM "MODEL"?
|   É a classe que REPRESENTA uma TABELA do banco de dados.
|   Ao invés de escrever SQL na mão ("SELECT * FROM users WHERE id=1")
|   você utiliza métodos elegantes da ORM.
|
| ORM = Object-Relational Mapper (Mapeador Objeto-Relacional)
|   Traduz o "mundo relacional" (tabelas/colunas/linhas) para o
|   "mundo orientado a objetos" (classes/objetos/atributos) e vice-versa.
|
| #####################################################
|  PADRÃO ACTIVE RECORD vs DATA MAPPER                #
| #####################################################
|   • ACTIVE RECORD (usado pelo Eloquent):
|       - O model SABE como se salvar no BD:
|         $user = new User; $user->name = 'João'; $user->save(); ✅
|       - Classe = regra de negócio + persistência, tudo junto.
|       - Muito produtivo (padrão do Rails/Laravel).
|
|   • DATA MAPPER (Doctrine no Symfony):
|       - Model = "classe POG" (plain old PHP object) sem saber de BD.
|       - Um EntityManager separado faz o CRUD.
|       - Melhor para domínios MUITO complexos.
|
| CARACTERÍSTICAS IMPORTANTES:
|   • Por padrão, o model "User" mapeia a tabela "users" (plural em inglês)
|     → pode sobrescrever com protected $table = 'tb_usuario';
|   • Assume que "id" é a chave primária (incrementável).
|   • Atributos mágicos: $user->name acessa a coluna 'name' diretamente.
|   • Herda de Model (Authenticatable neste caso, que adiciona login).
|
| 🔐 TRAITS UTILIZADOS:
|   • HasApiTokens: adiciona métodos ao model para:
|       - criar token: $user->createToken('meu-token')->plainTextToken
|       - ver tokens criados na tabela personal_access_tokens
|     É a base da autenticação por Bearer Token (API stateless).
|
|   • HasFactory: permite gerar registros fake (testes/seeding).
|
|   • Notifiable: pode enviar notificações (emails, etc.).
|##########################################################################
*/
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 🏷️ Mass Assignment (Atribuição em Massa)
     *
     * Por SEGURANÇA, o Laravel NÃO deixa você fazer:
     *    $user = User::create($request->all());
     * ... a menos que defina $fillable ou $guarded.
     *
     * Isso evita Mass Assignment Attack:
     *   se um atacante malicioso mandar is_admin=1 no POST,
     *   e você não prevenir, o usuário vira admin!
     *
     *   $fillable  = "ALLOW LIST" — apenas esses campos podem ser preenchidos
     *                                 via create() / fill().
     *   $guarded   = "BLOCK LIST" — esses NUNCA podem ser preenchidos.
     *                                 Use UM ou OUTRO, nunca os dois.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * 🙈 Atributos OCULTOS durante serialização (toArray() / JSON).
     *
     * Sem isso, ao retornar User::find(1) na API, a senha HASH e o
     * remember_token seriam expostos no JSON para o mundo.
     * NUNCA, JAMAIS exponha senhas em respostas JSON.
     *
     * Alternativa moderna: API Resources (veremos em Task 3),
     * que oferecem controle FINO sobre quais campos irão na resposta.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * ⚙️ Attribute Casting
     *
     * Converte AUTOMATICAMENTE o valor do banco (string) para o tipo
     * PHP desejado ao ler, e de volta para string ao salvar.
     *
     * Casts comuns:
     *   'boolean', 'integer', 'float', 'string',
     *   'datetime' (→ objeto Carbon),
     *   'array', 'json', 'collection',
     *   Enum cast (PHP 8.1+)  — usaremos em Task 2.
     *
     * Sem esse cast, $user->email_verified_at seria um string cru;
     * Com esse cast, é um objeto Carbon (pode ->format('d/m/Y'), etc.).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /*
     * #####################################################
     *  RELAÇÕES (1:N) — Um usuário tem muitos...
     * #####################################################
     * Estas relações são MÉTODOS públicos, mas se você chamar
     * como PROPRIEDADE ($user->categories), o Laravel "lazy loads"
     * os dados e devolve uma Collection (lista) desses Models.
     * Isso é "Dynamic Property" do Eloquent.
     */

    /**
     * 🏷️ Categorias do usuário (User → 1:N → Categories)
     */
    public function categories()
    {
        return $this->hasMany(\App\Models\Category::class);
    }

    /**
     * 📝 Tarefas do usuário (User → 1:N → Tasks)
     */
    public function tasks()
    {
        return $this->hasMany(\App\Models\Task::class);
    }
}
