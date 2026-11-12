<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePostRequest;
use App\Http\Requests\Api\V1\UpdatePostRequest;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * List published posts (public)
     *
     * Newest first, 12 per page. Filter with `?category=<slug>`. No authentication.
     */
    public function publicIndex(Request $request): AnonymousResourceCollection
    {
        $posts = Post::query()->published()->with('category')
            ->when($request->query('category'), fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->orderByDesc('published_at')->orderByDesc('id')
            ->paginate(12);

        return PostResource::collection($posts);
    }

    /** Show a published post by slug (public) */
    public function publicShow(string $slug): PostResource
    {
        return new PostResource(Post::query()->published()->with('category')->where('slug', $slug)->firstOrFail());
    }

    /**
     * List posts
     *
     * All posts including drafts, newest first, 20 per page.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Post::class);

        return PostResource::collection(Post::query()->with('category')->orderByDesc('id')->paginate(20));
    }

    /** Show a post */
    public function show(Post $post): PostResource
    {
        Gate::authorize('view', $post);

        return new PostResource($post->load('category'));
    }

    /**
     * Create a post
     *
     * `body` is rich-text HTML and is sanitised server-side; disallowed tags, attributes and URL schemes are removed.
     */
    public function store(StorePostRequest $request): PostResource
    {
        return new PostResource(Post::query()->create($request->validated())->refresh()->load('category'));
    }

    /** Update a post */
    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $post->update($request->validated());

        return new PostResource($post->load('category'));
    }

    /** Delete a post */
    public function destroy(Post $post, MediaService $media): Response
    {
        Gate::authorize('delete', $post);

        $media->delete($post->image);
        $post->delete();

        return response()->noContent();
    }
}
