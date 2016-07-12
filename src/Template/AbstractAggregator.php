<?php

namespace Ludos\Template;

use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAggregator implements AggregatorInterface
{
    protected $templateNames = [
        'app::home',
        'app::user',
        'app::game',
        'app::games',
        'app::error',
        'app::about-us',
        'app::terms-of-use',
        'app::privacy-policy',
        'app::confirmed'
    ];
    
    /** @var ServerRequestInterface $request */
    protected $request;

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getTemplateNames(): array
    {
        return $this->templateNames;
    }

    public function hasTemplateName(string $templateName): bool
    {
        return in_array($templateName, $this->templateNames, true);
    }
}
