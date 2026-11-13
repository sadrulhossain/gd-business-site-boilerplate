<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\AccessToken;

class ProfileController extends Controller
{
    /**
     * Update own profile
     *
     * `name`, and/or `password` with `current_password` and `password_confirmation`. Other sessions are signed out
     * when the password changes. Returns the same body as `GET /me`.
     */
    public function update(UpdateProfileRequest $request, MeController $me): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->safe()->only(['name', 'password']);

        $user->update($data);

        if (isset($data['password'])) {
            $token = $user->token();
            $current = $token instanceof AccessToken ? $token->id : null;
            $user->tokens()->when($current, fn ($q) => $q->where('id', '!=', $current))->update(['revoked' => true]);
        }

        return $me($request);
    }
}
