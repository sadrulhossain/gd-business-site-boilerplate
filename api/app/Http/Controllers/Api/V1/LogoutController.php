<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class LogoutController extends Controller
{
    /**
     * Sign out
     *
     * Revokes the current access token and its refresh tokens. The browser session on the
     * login page is ended separately by visiting `/logout`.
     */
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Token $token */
        $token = $user->token();

        RefreshToken::query()->where('access_token_id', $token->id)->update(['revoked' => true]);
        $token->revoke();

        return response()->noContent();
    }
}
