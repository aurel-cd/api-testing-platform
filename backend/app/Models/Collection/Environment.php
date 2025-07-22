<?php

namespace App\Models\Collection;

use App\Models\BaseModel;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $user_id
 * @property User $user
 * @property string $workspace_id
 * @property boolean $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Environment extends BaseModel
{

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function variables(): HasMany
    {
        return $this->hasMany(EnvironmentVariable::class, 'environment_id', 'id');
    }
}
