<?php

namespace Ludos\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class ShowInfo
{
    private $templateRenderer;

    private static $staticPages = [
        'privacy-policy',
        'terms-of-use',
        'about-us'
    ];

    private $serializer;

    public function __construct(TemplateRendererInterface $templateRenderer, Serializer $serializer)
    {
        $this->templateRenderer = $templateRenderer;
        $this->serializer = $serializer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $staticPageName = $request->getAttribute('staticPageName');
        if ($staticPageName === null || !in_array($staticPageName, self::$staticPages, true)) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        return new HtmlResponse($this->templateRenderer->render("app::{$staticPageName}"));
    }
}
