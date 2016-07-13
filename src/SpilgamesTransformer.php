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

use Cocur\Slugify\Slugify;
use GuzzleHttp\Client;
use Ludos\Entity\Game\Description;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Game\Meta;
use Ludos\Entity\Locale;
use Ludos\Entity\Provider\Category;
use Ludos\Entity\Provider\Provider;

class SpilgamesTransformer
{
    private $locale;
    private $provider;
    private $client;
    private $slugify;

    public function __construct(Provider $provider, Locale $locale, Client $client = null)
    {
        $this->provider = $provider;
        $this->locale = $locale;
        $this->slugify = new Slugify();
        $this->client = $client ?? new Client();
    }

    public function __invoke($entry)
    {
        if ($entry === null || !array_key_exists('gameUrl', $entry)) {
            throw new \RuntimeException(sprintf('Cannot build game from provided data: %s', var_export($entry, true)));
        }
        if ($this->provider === null) {
            throw new \RuntimeException('Provider is not set');
        }
        if ($this->locale === null) {
            throw new \RuntimeException('Locale is not set');
        }

        $meta = new Meta();
        $meta->setId($entry['id'])
            ->setData(json_encode($entry))
            ->setProvider($this->provider);
        
        $description = new Description();
        $description->setLocale($this->locale)
            ->setTranslation($entry['description']);

        $thumbnailUrl = $this->getThumbnailUrl($entry);

        $game = new Game();
        $game->setName($entry['title'])
            ->setSlug($this->slugify->slugify($entry['title']))
            ->setThumbnail($thumbnailUrl)
            ->addMeta($meta)
            ->addDescription($description)
            ->setUrl($entry['gameUrl'])
            ->setWidth($entry['width'])
            ->setHeight($entry['height'])
            ->setType($this->getType($entry));

        if (isset($entry['category'])) {
            $game->addCategory((new Category())->setName($entry['category']));
        }
        if (isset($entry['subcategory']) && $entry['subcategory'] !== $entry['category']) {
            $game->addCategory((new Category())->setName($entry['subcategory']));
        }

        return $game;
    }

    private function getThumbnailUrl($entry)
    {
        if (!array_key_exists('thumbnails', $entry)) {
            return null;
        }

        foreach (['large', 'medium', 'small'] as $field) {
            if (!array_key_exists($field, $entry['thumbnails'])
                || stripos($entry['thumbnails'][$field], 'null') !== false
            ) {
                continue;
            }
            return $entry['thumbnails'][$field];
        }

        return null;
    }

    /**
     * Type detection is based on Spilgames game URL
     * @param array $entry
     * @return null|string
     * @throws \RuntimeException
     */
    private function getType(array $entry)
    {
        if (!array_key_exists('gameUrl', $entry)) {
            return null;
        }
        $url = $entry['gameUrl'];
        if (stripos($url, '/html5/') !== false) {
            return Game::HTML5;
        }
        if (stripos($url, '/flash/') !== false || stripos($url, '.swf') !== false) {
            return Game::FLASH;
        }

        // Make request to a game page and check wether it's made with "Unity WebGL" or "Unity Webplayer"
        // "Unity WebGL" uses canvas, so it's HTML5 type
        // "Unity Webplayer" is a plugin, so we return proper type
        if (stripos($url, '/unity/') !== false
            && stripos($this->client->get($url)->getBody()->getContents(), 'webplayer') !== false
        ) {
            return Game::UNITY;
        }
        return Game::HTML5;
    }
}
