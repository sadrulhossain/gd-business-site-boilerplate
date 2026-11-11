<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\MenuTree;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeMenusController extends Controller
{
    /**
     * Current user's menus
     *
     * The navigation tree filtered by the user's permissions.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(['data' => MenuTree::for($user)]);
    }
}
