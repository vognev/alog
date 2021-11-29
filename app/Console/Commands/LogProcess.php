<?php

namespace App\Console\Commands;

use App\Models\Entry;
use App\Models\Log;
use App\Models\Network;
use App\Services\Geoip;
use Illuminate\Console\Command;

class LogProcess extends Command
{
    protected $signature = 'log:process {id : Log ID}';

    public function handle(Geoip $geoip)
    {
        /** @var Log $log */
        $log = Log::query()->findOrFail($this->argument('id'));

        /** @var Entry $entry */
        while($entry = $log->entries()->whereNull('network_id')->first()) {
            /** @var Network $network */
            $network = Network::query()
                ->where('address',   '<=', $entry->address)
                ->where('broadcast', '>=', $entry->address)
                ->first();

            if (! $network) {
                $this->info("Looking for {$entry->host}");
                $data = $geoip->lookup($entry->host);

                if (! $data || !count($data['cidrs']) || !count($data['names'])) {
                    $this->warn("No network for {$entry->host}");
                    $log->entries()->where('address', $entry->address)
                        ->update(['network_id' => 0]);
                    continue;
                }

                foreach ($data['cidrs'] as $cidr) {
                    $network = Network::create([
                        'name' => $data['names'][0],
                        'cidr' => $cidr,
                    ]);

                    $this->info("Processing network " . $network->name);
                    $this->info("Entries updated  - " . $network->updateEntries());
                }
            } else {
                $this->info("Processing network " . $network->name);
                $this->info("Entries updated  - " . $network->updateEntries());
            }
        }
    }
}
