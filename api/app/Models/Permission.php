<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    const TYPE_CREATE = 'create';
    const TYPE_READ = 'read';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';

    protected $fillable = [
        'name',
        'type',
        'group',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public static function validTypes(): array
    {
        return [
            self::TYPE_CREATE,
            self::TYPE_READ,
            self::TYPE_UPDATE,
            self::TYPE_DELETE,
        ];
    }
}
