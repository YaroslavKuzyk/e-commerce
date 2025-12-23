<?php

namespace App\Services\Admin;

use App\Contracts\Services\Admin\PermissionServiceInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;

class AdminPermissionService implements PermissionServiceInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository
    ) {}

    public function getAll()
    {
        return $this->permissionRepository->getAll();
    }
}
