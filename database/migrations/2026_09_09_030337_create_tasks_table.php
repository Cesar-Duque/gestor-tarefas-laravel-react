<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|##########################################################################
| # TEORIA: Integridade Referencial + Tipos de Dados SQL                 #
|##########################################################################
|
| Esta migration cria a tabela 'tasks' (tarefas), que tem DUAS relações:
|
|     users  1 ──────── N tasks  (um usuário → muitas tarefas)
|
|     categories  1 ─── N tasks  (uma categoria → muitas tarefas)
|
| Observação IMPORTANTE sobre cardinalidade:
|   - user_id é OBRIGATÓRIO (NOT NULL): uma tarefa SEMPRE tem um dono.
|   - category_id é OPCIONAL (nullable): tarefa pode não ter categoria
|     (ex: "comprar pão" não precisa de classificação).
|
| ENUMs vs Lookup Tables:
|   • Usamos ENUM aqui para status/prioridade por simplicidade.
|   • PRO de ENUM: rápido, SQL nível, consome pouco.
|   • CON de ENUM: adicionar novo valor = ALTER TABLE (lento em BD grande).
|   • Alternativa: tabela 'statuses' + FK (lookup table).
|
| ÍNDICES (INDEX):
|   Colunas usadas em WHERE/FILTROS devem ser indexadas para performance.
|   Sem índice o MySQL faz FULL TABLE SCAN (lê todas as linhas).
|   Com índice = B-tree lookup O(log n).
|##########################################################################
*/
class CreateTasksTable extends Migration
{
    /**
     * Cria a tabela de tarefas.
     *
     * Campos escolhidos (baseados em FR-3 do spec.md):
     *   - Título, descrição, status (pendente, em_progresso, concluida)
     *   - Prioridade (baixa, média, alta)
     *   - Data de vencimento (due_date), data de conclusão (completed_at)
     *   - FKs para user_id e category_id
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {

            $table->id();

            // 🔗 FK Usuário (obrigatória, cascade delete)
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // 🔗 FK Categoria (OPCIONAL → nullable; onDelete set null = se
            // a categoria for apagada, não apaga as tarefas)
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->string('title', 150);                        // Título curto (obrigatório)
            $table->text('description')->nullable();             // Descrição detalhada (opcional)

            /* Status da tarefa:
             *   pendente     = ainda não começou
             *   em_progresso = em andamento
             *   concluida    = finalizada
             */
            $table->enum('status', ['pendente', 'em_progresso', 'concluida'])
                ->default('pendente');

            /* Prioridade:
             *   baixa  = pode esperar
             *   media  = normal
             *   alta   = urgente
             */
            $table->enum('prioridade', ['baixa', 'media', 'alta'])
                ->default('media');

            $table->date('due_date')->nullable();               // Data de vencimento (YYYY-MM-DD)
            $table->timestamp('completed_at')->nullable();      // Data/HORA de quando foi concluída

            $table->timestamps();

            // 🚀 ÍNDICES para performance em filtros
            $table->index(['user_id']);                          // Busca tarefas do usuário
            $table->index(['status']);                           // Filtrar por status
            $table->index(['prioridade']);                       // Filtrar por prioridade
            $table->index(['due_date']);                         // Ordenar por vencimento
            $table->index(['category_id']);                      // Filtrar por categoria
            // Índice composto (multi-coluna): buscas com VÁRIOS filtros juntos
            $table->index(['user_id', 'status', 'due_date']);
        });
    }

    /**
     * Reverte (ROLLBACK): apaga a tabela tasks.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
