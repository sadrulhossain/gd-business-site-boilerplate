<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Base class for module seeders. Every module seeds its own permissions,
 * menu rows and role grants; register it in DatabaseSeeder.
 *
 * Menu node: ['title' => string, 'permission' => ?string, 'path' => ?string,
 *             'icon' => ?string, 'sort' => ?int, 'children' => list<node>]
 * A node without 'permission' is a group.
 */
abstract class ModuleSeeder extends Seeder
{
    public const GUARD = 'api';

    /** @return list<string> */
    abstract protected function permissions(): array;

    /** @return list<array<string, mixed>> */
    abstract protected function menus(): array;

    /**
     * Role name => permissions granted to it (additive, never revokes).
     *
     * @return array<string, list<string>>
     */
    protected function grants(): array
    {
        return [];
    }

    public function run(): void
    {
        foreach ($this->permissions() as $name) {
            Permission::findOrCreate($name, self::GUARD);
        }

        $this->seedMenus($this->menus(), null);

        // Permissions were just created: drop the cached list so givePermissionTo() can find them.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->grants() as $role => $permissions) {
            Role::findOrCreate($role, self::GUARD)->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @param  list<array<string, mixed>>  $nodes */
    private function seedMenus(array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $i => $node) {
            $permission = $node['permission'] ?? null;

            $menu = Menu::query()->updateOrCreate(
                $permission !== null
                    ? ['permission_name' => $permission]
                    : ['permission_name' => null, 'parent_id' => $parentId, 'title' => $node['title']],
                [
                    'parent_id' => $parentId,
                    'title' => $node['title'],
                    'path' => $node['path'] ?? null,
                    'icon' => $node['icon'] ?? null,
                    'sort' => $node['sort'] ?? ($i + 1) * 10,
                ],
            );

            $this->seedMenus($node['children'] ?? [], $menu->id);
        }
    }
}
