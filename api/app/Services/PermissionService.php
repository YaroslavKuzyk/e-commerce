<?php

namespace App\Services;

use App\Contracts\Services\PermissionServiceInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;

class PermissionService implements PermissionServiceInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository
    ) {}

    public function getAll()
    {
        return $this->permissionRepository->getAll();
    }
}
