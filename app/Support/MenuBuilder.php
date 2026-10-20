<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

class MenuBuilder
{
    /**
     * @return list<array{key: string, label: string, icon: string, route: string}>
     */
    public function for(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        /** @var list<array{key: string, label: string, icon: string, route: string, permissions: list<string>}> $items */
        $items = config('propix.menus');
        $menu = [];

        foreach ($items as $item) {
            if (! $this->visible($user, $item['permissions'])) {
                continue;
            }

            $menu[] = [
                'key' => $item['key'],
                'label' => __($item['label']),
                'icon' => $item['icon'],
                'route' => route($item['route']),
            ];
        }

        return $menu;
    }

    /**
     * @param  list<string>  $permissions
     */
    private function visible(User $user, array $permissions): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        return $user->hasAnyPermission($permissions);
    }
}
