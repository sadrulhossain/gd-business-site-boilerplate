<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Builds the navigation tree a user is allowed to see.
 *
 * A leaf is visible when the user can() its permission. A group (no
 * permission) is visible only when it has at least one visible child.
 * The superadmin passes every can() through Gate::before, so sees everything.
 */
final class MenuTree
{
    /**
     * @return list<array{id: int, title: string, icon: string|null, path: string|null, permission_name: string|null, children: list<array<string, mixed>>}>
     */
    public static function for(User $user): array
    {
        /** @var Collection<int|string, Collection<int, Menu>> $byParent */
        $byParent = Menu::query()->orderBy('sort')->orderBy('id')->get()->groupBy(fn (Menu $menu) => $menu->parent_id ?? 0);

        return self::branch($user, $byParent, 0);
    }

    /**
     * @param  Collection<int|string, Collection<int, Menu>>  $byParent
     * @return list<array{id: int, title: string, icon: string|null, path: string|null, permission_name: string|null, children: list<array<string, mixed>>}>
     */
    private static function branch(User $user, Collection $byParent, int $parentId): array
    {
        $items = [];

        foreach ($byParent->get($parentId, collect()) as $menu) {
            $children = self::branch($user, $byParent, $menu->id);

            $visible = $menu->permission_name === null
                ? $children !== []
                : $user->can($menu->permission_name);

            if (! $visible) {
                continue;
            }

            $items[] = [
                'id' => $menu->id,
                'title' => $menu->title,
                'icon' => $menu->icon,
                'path' => $menu->path,
                'permission_name' => $menu->permission_name,
                'children' => $children,
            ];
        }

        return $items;
    }
}
