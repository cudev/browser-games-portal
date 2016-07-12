<?php

namespace Ludos\Container;

use Interop\Container\ContainerInterface;
use Ludos\Template\Aggregators\SocialSettingsAggregator;

class SocialSettingsAggregatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $socialSettings = [
            'facebook' => $container->get('config')['facebook'],
            'google' => $container->get('config')['google'],
            'twitter' => $container->get('config')['twitter']
        ];
        return new SocialSettingsAggregator($socialSettings);
    }
}