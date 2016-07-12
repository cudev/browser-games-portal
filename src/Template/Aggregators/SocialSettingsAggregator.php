<?php

namespace Ludos\Template\Aggregators;

use Ludos\Template\AbstractAggregator;

class SocialSettingsAggregator extends AbstractAggregator
{
    private $socialSettings;

    public function __construct($socialSettings)
    {
        $this->socialSettings = $socialSettings;
    }

    public function getTemplateVariables(): array
    {
        return ['socialSettings' => $this->socialSettings];
    }
}