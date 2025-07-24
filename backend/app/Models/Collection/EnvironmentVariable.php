<?php

namespace App\Models\Collection;

use App\Enums\Environment\VariableType;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $environment_id
 * @property Environment $environment
 * @property string $key
 * @property string $value
 * @property VariableType $type
 * @property boolean $is_active
 * @property boolean $is_secret
 */
class EnvironmentVariable extends BaseModel
{

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class, 'environment_id', 'id');
    }
}
