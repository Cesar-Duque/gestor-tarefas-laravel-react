<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Subtask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SubtaskController — Gerenciamento de subtarefas vinculadas a uma tarefa.
 *
 * 🔎 Funcionalidades:
 *   • POST /api/tasks/{task}/subtasks → criar subtarefa
 *   • PATCH /api/subtasks/{subtask}/toggle → alternar concluída/pendente
 *   • DELETE /api/subtasks/{subtask} → excluir subtarefa
 */
class SubtaskController extends Controller
{
    /**
     * ➕ CRIAR subtarefa vinculada a uma Task.
     * POST /api/tasks/{task}/subtasks → 201 Created
     */
    public function store(Request $request, Task $task): JsonResponse
    {
        // Se desejar, você pode mover essa validação para um FormRequest (ex: StoreSubtaskRequest)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // Garante que o usuário autenticado é o dono da tarefa pai
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Ação não autorizada.'], 403);
        }

        // Cria a subtarefa através do relacionamento
        $subtask = $task->subtasks()->create([
            'title' => $validated['title'],
        ]);

        return response()->json([
            'message' => 'Subtarefa criada com sucesso.',
            'data'    => $subtask,
        ], 201);
    }

    /**
     * ✅ Toggle rápido CONCLUÍDA / PENDENTE para a subtarefa
     * PATCH /api/subtasks/{subtask}/toggle
     */
    public function toggleComplete(Request $request, Subtask $subtask): JsonResponse
    {
        // Carrega o relacionamento da tarefa pai para validar o dono
        $task = $subtask->task;

        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Ação não autorizada.'], 403);
        }

        // Inverte o status booleano atual
        $subtask->update([
            'is_completed' => !$subtask->is_completed
        ]);

        return response()->json([
            'message' => 'Status da subtarefa atualizado.',
            'data'    => $subtask,
        ], 200);
    }

    /**
     * 🗑️ EXCLUIR uma subtarefa.
     * DELETE /api/subtasks/{subtask}
     */
    public function destroy(Request $request, Subtask $subtask): JsonResponse
    {
        $task = $subtask->task;

        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Ação não autorizada.'], 403);
        }

        $subtask->delete();

        return response()->json([
            'message' => 'Subtarefa excluída com sucesso.',
        ], 200);
    }
}
