<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePostImageRequest;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\Services\MediaService;
use Illuminate\Support\Facades\Gate;

class PostImageController extends Controller
{
    /**
     * Upload a post image
     *
     * Multipart field `image` (jpeg, png or webp). Replaces the current image;
     * WebP variants are generated in the background.
     */
    public function store(StorePostImageRequest $request, Post $post, MediaService $media): PostResource
    {
        $previous = $post->image;

        $post->update(['image' => $media->store($request->file('image'), 'posts')]);
        $media->delete($previous);

        return new PostResource($post);
    }

    /** Remove the post image */
    public function destroy(Post $post, MediaService $media): PostResource
    {
        Gate::authorize('update', $post);

        $media->delete($post->image);
        $post->update(['image' => null]);

        return new PostResource($post);
    }
}
