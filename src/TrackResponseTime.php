<?php

namespace Ludos;

use Predis\Client;

class TrackResponseTime
{
    private $client;

    const KEY_HITS = 'track:hits';
    const KEY_AVERAGE_TIME = 'track:average-time';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke()
    {
        $currentTime = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000);
        $hits = $this->client->get(self::KEY_HITS) ?? 0;
        $averageTime = $this->client->get(self::KEY_AVERAGE_TIME) ?: $currentTime;

        $newAverageTime = round(
            ($averageTime * $hits + $currentTime) / ($hits + 1)
        );

        $this->client->incr(self::KEY_HITS);
        $this->client->set(self::KEY_AVERAGE_TIME, $newAverageTime);
    }
}
