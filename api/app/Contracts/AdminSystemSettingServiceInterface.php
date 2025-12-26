<?php

namespace App\Contracts;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Collection;

interface AdminSystemSettingServiceInterface
{
    public function getAllSettings(): Collection;

    public function getAvailableTypes(): array;

    public function getSettingByType(string $type): ?SystemSetting;

    public function updateSetting(string $type, array $data): SystemSetting;

    public function toggleActive(string $type, bool $isActive): SystemSetting;

    public function deleteSetting(string $type): bool;
}
