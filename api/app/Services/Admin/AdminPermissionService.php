<?php

namespace App\Services\Admin;

use App\Contracts\Services\Admin\AdminPermissionServiceInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;

class AdminPermissionService implements AdminPermissionServiceInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository
    ) {}

    public function getAll()
    {
        return $this->permissionRepository->getAll();
    }
}
