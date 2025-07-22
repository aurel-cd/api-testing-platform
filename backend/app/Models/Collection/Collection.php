<?php

namespace App\Models\Collection;

use App\Enums\Collection\Visibility;
use App\Models\BaseModel;
use App\Models\User\User;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $user_id
 * @property User $user
 * @property string|null $parent_id
 * @property Collection|null $parent
 * @property Visibility $visibility
 * @property string $workspace_id
 * @property string $version
 * @property array $settings
 * @property integer $position
 * @property boolean $is_archived
 * @property Carbon $archived_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Collection extends BaseModel
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visibility' => Visibility::class,
            'archived_at' => 'datetime',
            'settings' => 'array'
        ];
    }
}
