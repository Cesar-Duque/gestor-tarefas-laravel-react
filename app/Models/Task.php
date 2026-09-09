<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
|##########################################################################
| # Model Task — Representa a tabela "tasks" (tarefas)                    #
|##########################################################################
|
|##########################################################################
| # TEORIA: Eager Loading vs Lazy Loading (N+1 Query Problem)              #
|##########################################################################
|
| Problema clássico N+1:
|   $categorias = Category::all();               // 1 query: SELECT * FROM categories
|   foreach ($categorias as $c) {
|       echo $c->tasks;                          // +1 query POR categoria!
|   }                                             // Total: 1 + N queries
|   Se tem 100 categorias → 101 queries! 💥
|
| SOLUÇÃO: Eager Loading (carregamento ANTECIPADO):
|   $categorias = Category::with('tasks')->get(); // 2 queries:
|                                                  //   1) SELECT * FROM categories
|                                                  //   2) SELECT * FROM tasks WHERE category_id IN (1,2,...)
|   foreach ($categorias as $c) echo $c->tasks;  // NÃO roda novas queries!
|
| Métodos:
|   with('relation')           → Eager load no SELECT
|   load('relation')           → Lazy Eager Load (após já ter buscado os models)
|   withCount('relation')      → Conta quantos tem, sem carregar tudo: $c->tasks_count
|##########################################################################
*/
class Task extends Model
{
    use HasFactory;

    /**
     * Atributos liberados para Mass Assignment.
     * 'user_id' é colocado pelo Service (não vem do input direto do usuário
     * por segurança — pegamos do usuário autenticado via Auth::id()).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'status',
        'prioridade',
        'due_date',
        'completed_at',
    ];

    /**
     * ⚙️ Conversão de TIPOS (Attribute Casting).
     *
     * - due_date:     campo date do BD vira objeto Carbon no PHP
     *                  (pode fazer ->format('d/m/Y'), ->isPast(), etc.)
     * - completed_at: timestamp → Carbon
     *
     * 🔎 Para ENUMs (status, prioridade), no Laravel 8 não temos native
     * Enum cast (só a partir de L9 + PHP 8.1). Aqui deixamos como string
     * e validamos os valores permitidos em Form Request.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date'     => 'date',
        'completed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | LOCAL SCOPES (Escopos Locais) — Reuso de condições de consulta
    |--------------------------------------------------------------------------
    |
    | ########################################################################
    | # TEORIA: Local Scopes do Eloquent                                     #
    | ########################################################################
    | Scopes = Trechos de query REUTILIZÁVEIS.
    * Ao invés de repetir:
    |    Task::where('user_id', auth()->id())->where('status', 'pendente')->get();
    |
    | Você escreve um método scopeXXX() e usa:
    |    Task::forCurrentUser()->pending()->get();
    |
    | Muito útil para DRY (Don't Repeat Yourself) + legibilidade.
    */

    /**
     * Escopo: SOMENTE tarefas do usuário autenticado.
     * Evita que o usuário veja tarefas de OUTROS usuários — segurança crucial.
     *
     * Uso: Task::forCurrentUser()->get();
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('tasks.user_id', auth()->id());
    }

    /**
     * Escopo: Tarefas por STATUS.
     * Ex: Task::status('pendente')->get();
     */
    public function scopeStatus($query, ?string $status)
    {
        if ($status) {
            return $query->where('tasks.status', $status);
        }
        return $query;
    }

    /**
     * Escopo: Tarefas por PRIORIDADE.
     */
    public function scopePrioridade($query, ?string $prioridade)
    {
        if ($prioridade) {
            return $query->where('tasks.prioridade', $prioridade);
        }
        return $query;
    }

    /**
     * Escopo: Filtrar por categoria.
     */
    public function scopeCategoryId($query, ?int $categoryId)
    {
        if ($categoryId) {
            return $query->where('tasks.category_id', $categoryId);
        }
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | RELAÇÕES
    |--------------------------------------------------------------------------
    */

    /**
     * 🧑 Usuário DONO desta tarefa (Tarefa → User).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 🏷️ Categoria desta tarefa (pode ser null → nullable).
     * Relação inversa de Category::tasks().
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
