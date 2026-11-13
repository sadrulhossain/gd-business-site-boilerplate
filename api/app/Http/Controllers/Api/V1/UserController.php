<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * List users
     *
     * Paginated, 15 per page, oldest first.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', User::class);

        return UserResource::collection(User::query()->with('roles')->orderBy('id')->paginate(15));
    }

    /** Show a user */
    public function show(User $user): UserResource
    {
        Gate::authorize('view', $user);

        return new UserResource($user->load('roles'));
    }

    /** Create a user */
    public function store(StoreUserRequest $request): UserResource
    {
        $data = $request->validated();
        $user = User::query()->create(collect($data)->only(['name', 'email', 'password'])->all());
        $user->syncRoles($data['roles'] ?? []);

        return new UserResource($user->load('roles'));
    }

    /**
     * Update a user
     *
     * A blank password keeps the current one. `roles` replaces the user's roles (the superadmin role is never touched here).
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $data = $request->validated();
        $fields = collect($data)->only(['name', 'email', 'password'])->all();

        if (($fields['password'] ?? null) === null) {
            unset($fields['password']);
        }

        $user->update($fields);

        if (array_key_exists('roles', $data)) {
            $keep = $user->isSuperadmin() ? [config('access.superadmin_role')] : [];
            $user->syncRoles([...$data['roles'], ...$keep]);
        }

        return new UserResource($user->load('roles'));
    }

    /** Delete a user (never yourself) */
    public function destroy(User $user): Response
    {
        Gate::authorize('delete', $user);
        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }
}
