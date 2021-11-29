<?php

namespace App\Services;

use App\Models\Network;
use MaxMind\Db\Reader;

class Geoip
{
    protected Reader $reader;

    public function __construct()
    {
        $this->reader = new Reader(base_path('data/GeoLite2-ASN.mmdb'));
    }

    public function lookup(string $ip) : ?array
    {
        $data = $this->reader->getWithPrefixLen($ip);

        if (is_null($data)) {
            return null;
        }

        [$name, $len] = $data;

        $netmask = (-1 << (32 - $len));
        $address = ip2long($ip) & $netmask;

        return [
            'cidrs' => [sprintf('%s/%s', long2ip($address), $len)],
            'names' => [$name['autonomous_system_organization']]
        ];
    }
}
