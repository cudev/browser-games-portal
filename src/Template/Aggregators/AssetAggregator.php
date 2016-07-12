<?php

namespace Ludos\Template\Aggregators;

use Ludos\Template\AbstractAggregator;
use Symfony\Component\Asset\UrlPackage;

class AssetAggregator extends AbstractAggregator
{
    private $urlPackage;

    public function __construct(UrlPackage $urlPackage)
    {
        $this->urlPackage = $urlPackage;
        $this->templateNames[] = 'dash::home';
        $this->templateNames[] = 'email::confirmation';
        $this->templateNames[] = 'email::notification';
    }

    public function getTemplateVariables(): array
    {
        return ['urlPackage' => $this->urlPackage];
    }
}
