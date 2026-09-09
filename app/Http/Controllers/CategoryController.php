<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * CategoryController = CRUD completo de categorias do usuário.
 *
 * 🔑 Todas as rotas aqui são protegidas por auth:sanctum (veja routes/api.php)
 * → request()->user() SEMPRE existe.
 */
class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService,
    ) {}

    /**
     * 📋 LISTAR categorias do usuário logado.
     * GET /api/categories
     *
     * @return AnonymousResourceCollection 200 OK
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $this->categoryService->findAllForUser($request->user()->id);

        /*
         * CategoryResource::collection() = converte Collection inteira
         * para um array de Resources JSON (sem meta de paginação, pois
         * categoria é lista curta, não precisa paginar)
         */
        return CategoryResource::collection($categories);
    }

    /**
     * ➕ CRIAR categoria.
     * POST /api/categories
     * 201 Created.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createForUser(
            $request->validated(),
            $request->user()->id,
        );

        return response()->json([
            'message'  => 'Categoria criada com sucesso.',
            'data'     => new CategoryResource($category),
        ], 201);
    }

    /**
     * 🔍 DETALHES de uma categoria específica (por ID).
     * GET /api/categories/{category}
     *
     * 🎓 TEORIA: Route Model Binding (implícito)
     *   No método usamos (Category $category). O Laravel automaticamente
     *   busca o model pelo ID da URL {category}.
     *   → se não existir: ModelNotFoundException → 404 JSON automático.
     *
     * ⚠️ AVISO: Apesar do binding trazer qualquer categoria pelo ID,
     * NÓS verificamos ownership via service (prevenção IDOR: Insecure
     * Direct Object Reference — não deixamos usuário ver a categoria
     * de outro usuário mexendo no ID da URL).
     */
    public function show(Request $request, int $category): CategoryResource
    {
        // Não usamos Route Model Binding aqui para aplicar filtro de user
        $cat = $this->categoryService->findByIdForUser($category, $request->user()->id);

        return new CategoryResource($cat);
    }

    /**
     * ✏️ ATUALIZAR categoria.
     * PUT/PATCH /api/categories/{category}
     */
    public function update(UpdateCategoryRequest $request, int $category): JsonResponse
    {
        $cat = $this->categoryService->updateForUser(
            $request->validated(),
            $category,
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Categoria atualizada com sucesso.',
            'data'    => new CategoryResource($cat),
        ], 200);
    }

    /**
     * 🗑️ EXCLUIR categoria.
     * DELETE /api/categories/{category}
     *
     * 204 No Content (sem body no response, padrão REST para delete).
     */
    public function destroy(Request $request, int $category): JsonResponse
    {
        $this->categoryService->deleteForUser($category, $request->user()->id);

        return response()->json([
            'message' => 'Categoria excluída com sucesso.',
        ], 200);
    }
}
