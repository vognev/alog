<?php

namespace App\Console\Commands;

use App\Models\Network;
use App\Services\Geoip;
use Illuminate\Console\Command;

class NetworkResolve extends Command
{
    protected $signature = 'network:resolve {addr : IP Address}';

    public function handle(Geoip $geoip)
    {
        $addr = $this->argument('addr');

        $exists = Network::query()->containsIP($addr)->first();

        if ($exists) {
            $this->info("Already belongs to: " . $exists->name);
            return self::SUCCESS;
        }

        $data = $geoip->lookup($addr);

        if (is_null($data) || ! count($data['cidrs'])) {
            $this->error('No networks found');
            return self::FAILURE;
        }

        foreach ($data['cidrs'] as $cidr) {
            $this->info('CIDR: ' . $cidr);
        }

        foreach ($data['names'] as $idx => $cidr) {
            $this->info('NAME '. $idx . ' : ' . $cidr);
        }

        $name = $this->ask('Which name to use?');

        $name = is_numeric($name) && isset($data['names'][$name])
            ? $data['names'][$name] : trim($name);

        foreach ($data['cidrs'] as $cidr) {
            /** @var Network $network */
            $network = Network::query()->updateOrCreate(['cidr' => $cidr], ['name' => $name]);
            $network->updateEntries();
        }

        return self::SUCCESS;
    }
}
