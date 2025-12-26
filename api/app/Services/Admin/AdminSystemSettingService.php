<?php

namespace App\Services\Admin;

use App\Contracts\AdminSystemSettingServiceInterface;
use App\Contracts\SystemSettingRepositoryInterface;
use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Collection;

class AdminSystemSettingService implements AdminSystemSettingServiceInterface
{
    public function __construct(
        private SystemSettingRepositoryInterface $repository
    ) {}

    public function getAllSettings(): Collection
    {
        return $this->repository->getAll();
    }

    public function getAvailableTypes(): array
    {
        $availableTypes = SystemSetting::getAvailableTypes();
        $existingSettings = $this->repository->getAll()->keyBy('type');

        $result = [];
        foreach ($availableTypes as $type => $meta) {
            $setting = $existingSettings->get($type);
            $result[] = [
                'type' => $type,
                'name' => $meta['name'],
                'name_uk' => $meta['name_uk'],
                'description' => $meta['description'],
                'description_uk' => $meta['description_uk'],
                'category' => $meta['category'],
                'is_configured' => $setting !== null,
                'is_active' => $setting?->is_active ?? false,
                'data' => $setting?->data ?? SystemSetting::getDefaultStructure($type),
                'default_structure' => SystemSetting::getDefaultStructure($type),
            ];
        }

        return $result;
    }

    public function getSettingByType(string $type): ?SystemSetting
    {
        return $this->repository->getByType($type);
    }

    public function updateSetting(string $type, array $data): SystemSetting
    {
        $availableTypes = SystemSetting::getAvailableTypes();

        if (!isset($availableTypes[$type])) {
            throw new \InvalidArgumentException("Invalid system setting type: {$type}");
        }

        $meta = $availableTypes[$type];

        return $this->repository->updateOrCreate($type, [
            'name' => $meta['name'],
            'name_uk' => $meta['name_uk'],
            'description' => $meta['description'],
            'description_uk' => $meta['description_uk'],
            'data' => $data['data'] ?? [],
            'is_active' => $data['is_active'] ?? false,
        ]);
    }

    public function toggleActive(string $type, bool $isActive): SystemSetting
    {
        $setting = $this->repository->getByType($type);

        if (!$setting) {
            throw new \InvalidArgumentException("System setting not found: {$type}");
        }

        return $this->repository->update($setting, ['is_active' => $isActive]);
    }

    public function deleteSetting(string $type): bool
    {
        $setting = $this->repository->getByType($type);

        if (!$setting) {
            return false;
        }

        return $this->repository->delete($setting);
    }
}
