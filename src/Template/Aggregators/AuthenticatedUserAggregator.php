<?php

namespace Ludos\Template\Aggregators;

use Ludos\Middleware\Authentication;
use Ludos\Template\AbstractAggregator;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;

class AuthenticatedUserAggregator extends AbstractAggregator
{
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
        $this->templateNames[] = 'app::confirmed';
    }

    public function getTemplateVariables(): array
    {
        $user = $this->request->getAttribute('user');
        return [
            'user' => $user,
            'serializedUser' => $this->serializer->serialize($user, 'json')
        ];
    }
}
