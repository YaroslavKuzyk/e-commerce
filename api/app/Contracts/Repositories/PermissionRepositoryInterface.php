<?php

namespace App\Contracts\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    public function getAll(): Collection;
}
