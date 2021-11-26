<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $cidr
 * @property int $address
 * @property int $broadcast
 * @method Network containsIP(string $ip)
 */
class Network extends Model
{
    protected $fillable = [
        'name',
        'cidr',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function(Network $network) {
            if (false !== strpos($network->cidr, '/')) {
                list ($address, $netmask) = explode ('/', $network->cidr);

                $netmask = ~((1 << (32 - $netmask)) - 1);
                $address = ip2long($address) & $netmask;

                $network->address   = $address &  $netmask;
                $network->broadcast = $address | ~$netmask;
            }

            if (false !== strpos($network->cidr, '-')) {
                list ($from, $till) = explode('-', $network->cidr);

                $network->address   = ip2long(trim($from));
                $network->broadcast = ip2long(trim($till));
            }
        });
    }

    public function updateEntries() : int
    {
        $query = Entry::query()
            ->where('address', '>=', $this->address)
            ->where('address', '<=', $this->broadcast);

        return $query->update(['network_id' => $this->id]);
    }

    public function scopeContainsIP(Builder $query, string $ip)
    {
        return $query->where(fn($q) =>
            $q->where('address',  '<=', ip2long($ip))
              ->where('broadcast','>=', ip2long($ip))
        );
    }
}
