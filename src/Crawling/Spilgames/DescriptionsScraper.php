<?php

namespace Ludos\Crawling\Spilgames;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DescriptionsScraper
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function scrapeDescriptions(string $portalUrl)
    {
        $foundDescriptions = [];
        $homePageHtml = $this->loadPage($portalUrl);
        $categoriesLinks = $this->getCategoriesLinks($homePageHtml);

        foreach ($categoriesLinks as $link) {
            $page = 1;
            $totalPages = null;
            do {
                $html = $this->loadPage($portalUrl . $link . '&page=' . $page);

                if ($totalPages === null) {
                    $totalPages = $this->parseTotalPages($html);
                }

                foreach ($this->parseDescriptions($html) as $remoteId => $description) {
                    if (in_array($remoteId, $foundDescriptions, true)) {
                        continue;
                    }
                    $foundDescriptions[] = $remoteId;
                    yield $remoteId => $description;
                }
            } while ($page++ <= $totalPages);
        }
    }

    private function loadPage($url, $tries = 4)
    {
        for ($try = 0; $try < $tries; $try++) {
            sleep(random_int(1, 5));
            try {
                return $this->client->get($url)->getBody()->getContents();
            } catch (GuzzleException $exception) {
                error_log($exception->getMessage());
            }
        }
    }

    private function parseDescriptions($html)
    {
        $matches = [];
        preg_match_all('/data-appid="(\d+)".*?class="tile-description">(.*?)</is', $html, $matches);
        return array_combine($matches[1], $matches[2]);
    }

    private function parseTotalPages($html)
    {
        $matches = [];
        preg_match_all('/class="pagination.*?\d+.+?(\d+).+?(\d+)/is', $html, $matches);
        return ceil($matches[2][0] / $matches[1][0]);
    }

    private function getCategoriesLinks($html)
    {
        $matches = [];
        preg_match_all('/href="(.+?sort=title)"/i', $html, $matches);
        return $matches[1];
    }
}
