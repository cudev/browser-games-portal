<?php

namespace Ludos\Action;

use Cudev\OrdinaryMail\EmailAddressEncryptor;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;

class EmailConfirm
{
    private $entityManager;
    private $urlHelper;
    private $templateRenderer;
    private $serializer;
    private $emailAddressEncryptor;

    public function __construct(
        EntityManager $entityManager,
        UrlHelper $urlHelper,
        TemplateRendererInterface $templateRenderer,
        Serializer $serializer,
        EmailAddressEncryptor $emailAddressEncryptor
    ) {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
        $this->templateRenderer = $templateRenderer;
        $this->serializer = $serializer;
        $this->emailAddressEncryptor = $emailAddressEncryptor;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $cryptedEmail = urldecode($request->getAttribute('cryptedEmail'));

        if ($cryptedEmail === null) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        $email = $this->emailAddressEncryptor->decrypt($cryptedEmail);

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user === null) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        $user->setEmailConfirmed(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new HtmlResponse($this->templateRenderer->render('app::confirmed'));
    }
}
