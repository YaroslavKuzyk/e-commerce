<?php

namespace App\Contracts;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Collection;

interface SystemSettingRepositoryInterface
{
    public function getAll(): Collection;

    public function getByType(string $type): ?SystemSetting;

    public function getByCategory(string $category): Collection;

    public function create(array $data): SystemSetting;

    public function update(SystemSetting $setting, array $data): SystemSetting;

    public function delete(SystemSetting $setting): bool;

    public function updateOrCreate(string $type, array $data): SystemSetting;
}
