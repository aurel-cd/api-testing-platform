<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BaseModel extends Model
{
    /**
     * Primary Key Type
     * @var string
     */
    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function($model){
            $model->id = Str::uuid();
        });
    }
}
