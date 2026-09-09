<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,

            /*
             * 🔢 Contador de tarefas: Se a relationship foi contada
             * com withCount('tasks'), automaticamente temos a coluna
             * tasks_count; se não, fica NULL (ou então com whenCounted).
             */
            'tasks_count' => $this->whenCounted('tasks'),

            'created_at'  => $this->created_at->toIso8601String(),
            'updated_at'  => $this->updated_at->toIso8601String(),
        ];
    }
}
