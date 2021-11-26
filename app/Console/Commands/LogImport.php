<?php

namespace App\Console\Commands;

use App\Models\Log;
use Illuminate\Console\Command;
use Kassner\LogParser\LogParser;

class LogImport extends Command
{
    protected $signature = 'log:import {name : Log name}';

    public function handle(LogParser $parser)
    {
        if (stream_isatty(STDIN)) {
            $this->error('Pipe log contents to STDIN');
            return self::FAILURE;
        }

        $name = $this->argument('name');

        $parser->setFormat('%h %l %u %t "%r".*');

        /** @var Log $oLog */
        $oLog = Log::query()->firstOrCreate([
            'name' => $name,
        ]);

        $oLog->entries()->delete();

        while($json = fgets(STDIN)) {
            $data = json_decode($json, true);
            $line = trim($data['log']);

            try {
                $entry = $parser->parse($line);
            } catch (\Exception $e) {
                continue;
            }

            if (
                !isset($entry->host) ||
                !isset($entry->stamp) ||
                !isset($entry->request)
            ) {
                continue;
            }

            list($method, $request) = explode(' ', $entry->request);

            $path = parse_url($request, PHP_URL_PATH);
            $query = parse_url($request, PHP_URL_QUERY);

            $oLog->entries()->create([
                'host'      => $entry->host,
                'stamp'     => $entry->stamp,
                'method'    => $method,
                'path'      => $path ?? $request,
                'query'     => $query,
            ]);
        }

        return self::SUCCESS;
    }
}
