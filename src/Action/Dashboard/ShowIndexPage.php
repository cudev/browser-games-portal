<?php

namespace Ludos\Action\Dashboard;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Ludos\Entity\Locale;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class ShowIndexPage
{
    protected $templateRenderer;
    protected $entityManager;
    protected $serializer;

    public function __construct(
        TemplateRendererInterface $templateRenderer,
        EntityManager $entityManager,
        Serializer $serializer
    ) {
        $this->templateRenderer = $templateRenderer;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $locales = $this->entityManager
            ->getRepository(Locale::class)
            ->findAll();
        $normalized = [];
        foreach ($locales as $locale) {
            $normalized[] = $this->serializer->normalize($locale);
        }
        $settings = [
            'locales' => $normalized
        ];

        return new HtmlResponse($this->templateRenderer->render('dash::home', ['settings' => $settings]));
    }
}
