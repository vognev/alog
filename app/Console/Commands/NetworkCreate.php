<?php

namespace App\Console\Commands;

use App\Models\Network;
use Illuminate\Console\Command;

class NetworkCreate extends Command
{
    protected $signature = 'network:create {name : Network name} {cidr : Network CIDR}';

    public function handle()
    {
        $name = $this->argument('name');
        $cidr = $this->argument('cidr');

        /** @var Network $network */
        $network = Network::query()->updateOrCreate(['cidr' => $cidr], ['name' => $name]);
        $this->info("Updated entries: " . $network->updateEntries());
    }
}
