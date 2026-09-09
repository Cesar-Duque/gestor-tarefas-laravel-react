<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/*
| TaskCollection: usado PAGINADO de tarefas.
|
| Quando você retorna TaskResource::collection() retorna só data[], sem
| metadados extras (links de paginação, etc.). Este Collection customizado
| permite ESTRUTURAR a resposta PADRONIZADA de paginação:
|   {
|     "data": [ ... tarefas ...],
|     "meta": {
|       "current_page": 1,
|       "total": 53,
|       "per_page": 10,
|       "last_page": 6,
|       ...
|     },
|     "links": { first, last, prev, next }
|   }
|
| Assim o React sabe exatamente como renderizar a paginação.
*/
class TaskCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        /*
         * $this->collection = LengthAwarePaginator com os Models.
         * Mapeamos cada item para TaskResource individualmente.
         */
        return [
            'data' => TaskResource::collection($this->collection),

            /* 📊 Meta-informações de paginação (para UI React) */
            'meta' => [
                'current_page'   => $this->currentPage(),
                'last_page'      => $this->lastPage(),
                'per_page'       => $this->perPage(),
                'total'          => $this->total(),
                'from'           => $this->firstItem(),
                'to'             => $this->lastItem(),
                'has_next_page'  => $this->hasMorePages(),
                'has_prev_page'  => $this->currentPage() > 1,
            ],

            /* 🔗 Links úteis (para UI usar, opcional, mas conveniente) */
            'links' => [
                'first' => $this->url(1),
                'last'  => $this->url($this->lastPage()),
                'prev'  => $this->previousPageUrl(),
                'next'  => $this->nextPageUrl(),
            ],
        ];
    }
}
