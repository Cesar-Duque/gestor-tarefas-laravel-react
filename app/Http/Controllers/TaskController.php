<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TaskController — CRUD de Tarefas do usuário autenticado.
 *
 * 🔎 Funcionalidades especiais:
 *   • GET /api/tasks → lista paginada com filtros e ordenação
 *   • PATCH /api/tasks/{task}/toggle → toggle concluída/não
 */
class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService,
    ) {}

    /**
     * 📋 LISTAR tarefas (paginação + filtros + ordenação)
     * GET /api/tasks?page=1&status=pendente&prioridade=alta&category_id=1&sort=due_date&dir=asc
     */
    public function index(Request $request): TaskCollection
    {
        /*
         * Parseamos todos os query params possíveis para o service.
         * Query params = tudo que vem após "?" na URL (ex: ?page=2&status=pendente)
         * são acessados via $request->query('campo') ou $request->get('campo').
         */
        $filters = [
            'status'      => $request->query('status'),
            'prioridade'  => $request->query('prioridade'),
            'category_id' => $request->filled('category_id')
                ? (int) $request->query('category_id')
                : null,
            'sort'        => $request->query('sort', 'created_at'),
            'dir'         => $request->query('dir', 'desc'),
            'per_page'    => $request->filled('per_page')
                ? (int) $request->query('per_page')
                : 10,
        ];

        $paginator = $this->taskService->findAllForUser($request->user()->id, $filters);

        /*
         * TaskCollection custom retorna data + meta de paginação + links.
         * Status 200 OK (padrão para leitura).
         */
        return new TaskCollection($paginator);
    }

    /**
     * ➕ CRIAR tarefa.
     * POST /api/tasks → 201 Created
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createForUser(
            $request->validated(),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Tarefa criada com sucesso.',
            'data'    => new TaskResource($task),
        ], 201);
    }

    /**
     * 🔍 EXIBIR detalhes de UMA tarefa.
     * GET /api/tasks/{task}
     */
    public function show(Request $request, int $task): TaskResource
    {
        return new TaskResource(
            $this->taskService->findByIdForUser($task, $request->user()->id)
        );
    }

    /**
     * ✏️ ATUALIZAR tarefa.
     * PUT/PATCH /api/tasks/{task}
     */
    public function update(UpdateTaskRequest $request, int $task): JsonResponse
    {
        $t = $this->taskService->updateForUser(
            $request->validated(),
            $task,
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Tarefa atualizada com sucesso.',
            'data'    => new TaskResource($t),
        ], 200);
    }

    /**
     * ✅ Toggle rápido CONCLUÍDA / PENDENTE
     * PATCH /api/tasks/{task}/toggle
     *
     * (Atalho comum em UI de tarefas: checkbox "marcar como feita")
     */
    public function toggleComplete(Request $request, int $task): JsonResponse
    {
        $t = $this->taskService->toggleComplete($task, $request->user()->id);

        return response()->json([
            'message' => 'Status da tarefa atualizado.',
            'data'    => new TaskResource($t),
        ], 200);
    }

    /**
     * 🗑️ EXCLUIR tarefa.
     * DELETE /api/tasks/{task}
     */
    public function destroy(Request $request, int $task): JsonResponse
    {
        $this->taskService->deleteForUser($task, $request->user()->id);

        return response()->json([
            'message' => 'Tarefa excluída com sucesso.',
        ], 200);
    }
}
