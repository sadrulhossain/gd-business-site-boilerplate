<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCategoryRequest;
use App\Http\Requests\Api\V1\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    /**
     * List blog categories (public)
     *
     * Ordered by `sort_order`. No authentication.
     */
    public function publicIndex(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::query()->orderBy('sort_order')->orderBy('id')->get());
    }

    /** List blog categories */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Category::class);

        return CategoryResource::collection(Category::query()->orderBy('sort_order')->orderBy('id')->get());
    }

    /** Show a blog category */
    public function show(Category $category): CategoryResource
    {
        Gate::authorize('view', $category);

        return new CategoryResource($category);
    }

    /** Create a blog category */
    public function store(StoreCategoryRequest $request): CategoryResource
    {
        // refresh(): load the DB defaults (sort_order) omitted from the request.
        return new CategoryResource(Category::query()->create($request->validated())->refresh());
    }

    /** Update a blog category */
    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Delete a blog category
     *
     * Its posts are kept and become uncategorised.
     */
    public function destroy(Category $category): Response
    {
        Gate::authorize('delete', $category);

        $category->delete();

        return response()->noContent();
    }
}
