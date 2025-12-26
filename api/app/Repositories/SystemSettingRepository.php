<?php

namespace App\Repositories;

use App\Contracts\SystemSettingRepositoryInterface;
use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Collection;

class SystemSettingRepository implements SystemSettingRepositoryInterface
{
    public function getAll(): Collection
    {
        return SystemSetting::orderBy('type')->get();
    }

    public function getByType(string $type): ?SystemSetting
    {
        return SystemSetting::where('type', $type)->first();
    }

    public function getByCategory(string $category): Collection
    {
        $types = collect(SystemSetting::getAvailableTypes())
            ->filter(fn($meta) => $meta['category'] === $category)
            ->keys()
            ->toArray();

        return SystemSetting::whereIn('type', $types)->get();
    }

    public function create(array $data): SystemSetting
    {
        return SystemSetting::create($data);
    }

    public function update(SystemSetting $setting, array $data): SystemSetting
    {
        $setting->update($data);
        return $setting->fresh();
    }

    public function delete(SystemSetting $setting): bool
    {
        return $setting->delete();
    }

    public function updateOrCreate(string $type, array $data): SystemSetting
    {
        return SystemSetting::updateOrCreate(
            ['type' => $type],
            $data
        );
    }
}
