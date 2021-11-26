<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Novutec\WhoisParser\Parser;

class Whois
{
    protected Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
        $this->parser->setCustomConfigFile(
            __DIR__ . '/whois.ini'
        );
    }

    public function lookup(string $ip) : ?array
    {
        $raw = Cache::rememberForever('whois:' . $ip, function() use($ip) {
            return $this->parser->lookup($ip)->rawdata[0] ?? '';
        });

        $lines = explode("\n", $raw);
        $blocks = [];

        $block = null;

        foreach ($lines as $line) {
            if (empty($line)) {
                if (! is_null($block)) {
                    $blocks[] = $block; $block = null;
                }
                continue;
            }

            if ('%' === $line[0] || '#' === $line[0]) continue;
            if (false === strpos($line, ':')) continue;

            $parts  = explode(':', $line, 2);
            $name   = trim($parts[0]);
            $value  = trim($parts[1]);

            if (is_null($block)) {
                $block = ['type' => $name, 'props' => []];
            }

            $block['props'][] = compact('name', 'value');
        }

        if (! is_null($block)) {
            $blocks[] = $block;
        }

        $names = []; $cidrs = [];

        value(function() use ($blocks, &$names, &$cidrs) {
            $inetnum = collect($blocks)->firstWhere('type', 'inetnum');
            if (! $inetnum) return;

            $name = data_get(collect($inetnum['props'])->firstWhere('name', 'netname'), 'value');
            if ($name && false === array_search($name, $names)) {
                $names[] = $name;
            }

            $name = data_get(collect($inetnum['props'])->firstWhere('name', 'descr'), 'value');
            if ($name && false === array_search($name, $names)) {
                $names[] = $name;
            }

            $inetnum = data_get(collect($inetnum['props'])->firstWhere('name', 'inetnum'), 'value');
            if ($inetnum) {
                foreach ($this->parseNetworks($inetnum) as $network) {
                    if (false === array_search($network, $cidrs)) {
                        $cidrs[] = $network;
                    }
                }
            }
        });

        return compact('names', 'cidrs');
    }

    protected function parseNetworks(string $networks) : array
    {
        $result = [];

        $networks = explode(',', $networks);

        foreach ($networks as $network) {
            $network = trim($network);

            if (false !== strpos($network, '/')) {
                $result[] = $network;
            }

            if (false !== strpos($network, '-')) {
                $result[] = $network;
            }
        }

        return $result;
    }
}
