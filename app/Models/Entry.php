<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

/**
 * @property DateTimeInterface $stamp
 * @property string $host
 * @property string $method
 * @property string $path
 * @property ?string $query
 * @property int $address
 * @property int $log_id
 * @property int $network_id
 */
class Entry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'host',
        'stamp',
        'method',
        'path',
        'query',
        'network_id',
    ];

    protected $casts = [
        'stamp' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function(Entry $entry) {
            $entry->address = ip2long($entry->host);
        });
    }
}
