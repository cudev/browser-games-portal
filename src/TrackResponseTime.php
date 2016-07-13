<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Cudev Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
