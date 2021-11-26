<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Log extends Model
{
    protected $fillable = [
        'name',
    ];

    public function entries() : HasMany
    {
        return $this->hasMany(Entry::class, 'log_id');
    }
}
