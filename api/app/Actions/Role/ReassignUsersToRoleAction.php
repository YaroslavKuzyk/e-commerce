<?php

namespace App\Actions\Role;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class ReassignUsersToRoleAction
{
    public function execute(Collection $users, Role $fromRole, Role $toRole): int
    {
        $count = 0;

        foreach ($users as $user) {
            $user->roles()->detach($fromRole->id);
            $user->assignRole($toRole);
            $count++;
        }

        return $count;
    }
}
