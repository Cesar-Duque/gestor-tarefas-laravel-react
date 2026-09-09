<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service para operações de negócio de Categorias.
 * Fica entre o Controller e o Model: toda lógica específica mora aqui.
 */
class CategoryService
{
    /**
     * Listar TODAS as categorias do usuário autenticado.
     * (Categoria é do usuário, filtrar SEMPRE por user_id para segurança)
     */
    public function findAllForUser(int $userId): Collection
    {
        /*
         * 🛡️ Segurança CRUCIAL:
         * SEMPRE filtre por user_id. Não deixe Category::all() ser usado,
         * pois retornaria categorias de OUTROS usuários.
         * Aqui aplicamos filtro user_id explícito.
         */
        return Category::where('user_id', $userId)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Buscar categoria por ID, garantindo que pertence ao usuário.
     * Falha com ModelNotFoundException se não encontrar.
     */
    public function findByIdForUser(int $categoryId, int $userId): Category
    {
        return Category::where('id', $categoryId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    /**
     * Criar uma categoria para o usuário.
     *
     * @param  array{name: string, description: ?string}  $validated
     */
    public function createForUser(array $validated, int $userId): Category
    {
        /*
         * 🛡️ user_id NÃO vem do request (usuário malicioso poderia
         * enviar user_id=2 para criar em nome de outro). NÓS mesmos
         * fixamos o user_id pelo usuário autenticado.
         */
        $category = new Category($validated);
        $category->user_id = $userId;
        $category->save();

        return $category->fresh();
    }

    /**
     * Atualizar categoria (com validação de ownership).
     */
    public function updateForUser(array $validated, int $categoryId, int $userId): Category
    {
        $category = $this->findByIdForUser($categoryId, $userId);
        $category->update($validated);

        return $category->fresh();
    }

    /**
     * Apagar categoria.
     * Se tiver tarefas, o migration tem onDelete('set null') → elas
     * ficam com category_id NULL (não se perdem).
     */
    public function deleteForUser(int $categoryId, int $userId): void
    {
        $category = $this->findByIdForUser($categoryId, $userId);
        $category->delete();
    }
}
