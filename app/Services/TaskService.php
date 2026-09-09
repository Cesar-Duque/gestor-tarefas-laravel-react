<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Service para lógica de negócio de Tarefas.
 *
 * Diferencia regra de negócio da camada de controle/roteamento.
 */
class TaskService
{
    /**
     * 🔍 Listar tarefas do usuário com PAGINAÇÃO + FILTROS + ORDENAÇÃO.
     *
     * @param  int  $userId        ID do usuário logado
     * @param  array{
     *     page: ?int,
     *     per_page: ?int,
     *     status: ?string,
     *     prioridade: ?string,
     *     category_id: ?int,
     *     sort: ?string,     // ex: "due_date:asc"
     *     dir: ?string       // asc|desc, usado separado
     * }  $filters
     * @return LengthAwarePaginator
     */
    public function findAllForUser(int $userId, array $filters): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 10;

        /*
         * 🔎 TEORIA: Paginação Server-side vs Client-side
         *   • Client-side: carrega TUDO, pagina no JS → funciona só para
         *     poucos registros. Com 10.000 tarefas = performance horrível.
         *   • Server-side: LIMIT + OFFSET no SQL → apenas 10 registros por
         *     página são transferidos. Ideal para apps reais.
         *
         * $query = Task::query() → Builder (query ainda NÃO executada)
         * → aplicamos condições dinâmicas (where) conforme filtros
         * → no final, paginate() executa SQL com LIMIT/OFFSET e devolve
         *   objeto LengthAwarePaginator com dados + meta (total, current_page...)
         */
        $query = Task::query()
            ->with('category')
            ->with('subtasks')     // EAGER LOAD (evita N+1 ao buscar categoria da tarefa)
            ->forCurrentUser()      // Scope: user_id = $userId (já usa auth()->id())
            ->status($filters['status'] ?? null)
            ->prioridade($filters['prioridade'] ?? null)
            ->categoryId($filters['category_id'] ?? null);

        /*
         * 🔀 ORDENAÇÃO: parseamos "sort" em "coluna|direção"
         * Ex: sort=due_date:asc  → ORDER BY due_date ASC
         *     sort=prioridade:desc → order by FIELD(prioridade,...)
         * Para prioridade (baixa/media/alta) precisamos de custom order:
         *   usamos ORDER BY FIELD(prioridade, 'alta','media','baixa')
         */
        $sort = $filters['sort'] ?? 'created_at';
        $dir  = $filters['dir']  ?? 'desc';
        $dir  = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['created_at', 'updated_at', 'due_date', 'title'];
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Caso especial de ordenação por prioridade (não alfabética)
        if ($sort === 'prioridade') {
            $query->orderByRaw("FIELD(prioridade, 'alta','media','baixa') {$dir}");
        } else {
            // Tratar NULL no due_date (MySQL coloca NULLs primeiro em ASC)
            if ($sort === 'due_date') {
                $query->orderByRaw("ISNULL(due_date) {$dir}");
            }
            $query->orderBy($sort, $dir);
        }

        /*
         * Executa SELECT com LIMIT 10 OFFSET ((page-1)*10)
         * Retorna LengthAwarePaginator: toArray() gera:
         *   { data: [...], current_page: 1, total: 53, per_page: 10,
         *     last_page: 6, from: 1, to: 10, links: {...} }
         */
        return $query->paginate($perPage);
    }

    /**
     * Buscar tarefa por ID do usuário (ownership).
     */
    public function findByIdForUser(int $taskId, int $userId): Task
    {
        return Task::with('category')
            ->where('id', $taskId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    /**
     * Criar tarefa.
     *
     * @param  array{
     *     title: string, description: ?string, category_id: ?int,
     *     status: string, prioridade: string, due_date: ?string
     * }  $validated
     */
    public function createForUser(array $validated, int $userId): Task
    {
        $task = new Task($validated);
        $task->user_id = $userId;

        /*
         * ⚠️ LÓGICA DE NEGÓCIO: se status = 'concluida', automaticamente
         * marca completed_at com data/hora AGORA (Carbon now).
         * Isso é REGRA DE NEGÓCIO → fica no SERVICE, NÃO no controller.
         */
        if ($validated['status'] === 'concluida') {
            $task->completed_at = Carbon::now();
        }

        $task->save();

        return $task->fresh(['category']);
    }

    /**
     * Atualizar tarefa.
     */
    public function updateForUser(array $validated, int $taskId, int $userId): Task
    {
        $task = $this->findByIdForUser($taskId, $userId);

        $statusChanged = ($task->status !== $validated['status']);

        $task->fill($validated);

        /* Regra de negócio:
         *   - Se mudou para concluída → seta completed_at
         *   - Se mudou de concluída para outro → limpa completed_at
         */
        if ($statusChanged) {
            if ($validated['status'] === 'concluida') {
                $task->completed_at = Carbon::now();
            } else {
                $task->completed_at = null;
            }
        }

        $task->save();

        return $task->fresh(['category']);
    }

    /**
     * Toggle rápido (muda status de uma tarefa para concluída ou não).
     * Ex: checkbox na UI marcar/desmarcar.
     */
    public function toggleComplete(int $taskId, int $userId): Task
    {
        $task = $this->findByIdForUser($taskId, $userId);

        if ($task->status === 'concluida') {
            $task->status       = 'pendente';
            $task->completed_at = null;
        } else {
            $task->status       = 'concluida';
            $task->completed_at = Carbon::now();
        }
        $task->save();

        return $task->fresh(['category']);
    }

    /**
     * Apagar tarefa (do usuário dono).
     */
    public function deleteForUser(int $taskId, int $userId): void
    {
        $task = $this->findByIdForUser($taskId, $userId);
        $task->delete();
    }
}
