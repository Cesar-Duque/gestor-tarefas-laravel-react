<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,

            // Status e prioridade (ENUM como string)
            'status'      => $this->status,
            'prioridade'  => $this->prioridade,

            // Data de vencimento formatada (YYYY-MM-DD)
            'due_date'    => $this->due_date
                ? $this->due_date->format('Y-m-d')
                : null,

            // Data de conclusão
            'completed_at' => $this->completed_at
                ? $this->completed_at->toIso8601String()
                : null,

            /*
             * 🔗 Relação com Category: quando carregada (eager load)
             * retorna um CategoryResource aninhado.
             * Sem whenLoaded → não incluímos o campo (evita objeto null).
             */
            'category' => $this->whenLoaded('category', function () {
                return $this->category ? new CategoryResource($this->category) : null;
            }),

            'subtasks' => $this->subtasks,

            // Campo amigável para UI: "vence em 2 dias", "vencida", etc.
            // Criado como campo calculado (Accessor virtual não, mas sim aqui)
            'meta' => [
                'is_overdue'     => $this->isOverdue(),
                'is_completed'   => $this->status === 'concluida',
                'priority_label' => $this->getPrioridadeLabel(),
                'status_label'   => $this->getStatusLabel(),
            ],

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    /**
     * Método auxiliar: tarefa está ATRASADA?
     * → due_date não nula, due_date < hoje, e não concluída.
     */
    protected function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && $this->status !== 'concluida';
    }

    /**
     * Label amigável de prioridade para UI em português.
     */
    protected function getPrioridadeLabel(): string
    {
        return match ($this->prioridade) {
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta'  => 'Alta',
            default => ucfirst($this->prioridade),
        };
    }

    /**
     * Status label amigável.
     */
    protected function getStatusLabel(): string
    {
        return match ($this->status) {
            'pendente'      => 'Pendente',
            'em_progresso'  => 'Em Progresso',
            'concluida'     => 'Concluída',
            default         => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
