<?php

namespace Ludos\Action;

use Cudev\OrdinaryMail\EmailAddressEncryptor;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class ShowLetter
{
    private $templateRenderer;
    private $entityManager;
    private $emailAddressEncryptor;

    public function __construct(
        TemplateRendererInterface $templateRenderer,
        EntityManager $entityManager,
        EmailAddressEncryptor $emailAddressEncryptor
    ) {
        $this->templateRenderer = $templateRenderer;
        $this->entityManager = $entityManager;
        $this->emailAddressEncryptor = $emailAddressEncryptor;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $letterType = $request->getAttribute('letterType');
        $cryptedEmail = urldecode($request->getAttribute('cryptedEmail'));

        $email = $this->emailAddressEncryptor->decrypt($cryptedEmail);

        if ($letterType === null || $letterType !== 'confirm' || $cryptedEmail === null) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user === null) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        $html = $this->templateRenderer->render(
            'email::confirmation',
            [
                'user' => $user,
                'letterType' => $letterType,
                'cryptedEmail' => $cryptedEmail,
                'isInBrowser' => true
            ]
        );

        $inliner = new CssToInlineStyles($html);
        $inliner->setUseInlineStylesBlock(true);
        $inliner->setCleanup(true);
        $inliner->setStripOriginalStyleTags(true);

        return new HtmlResponse($inliner->convert());
    }
}
