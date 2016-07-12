<?php

namespace Ludos\Action;

use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Google_Client as GoogleClient;
use Google_Service_Oauth2 as GoogleOauth2;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;

class SignInGoogle
{
    protected $entityManager;
    protected $session;
    protected $serverUrlHelper;
    protected $urlHelper;
    protected $googleClient;

    public function __construct(
        EntityManager $entityManager,
        Session $session,
        ServerUrlHelper $serverUrlHelper,
        UrlHelper $urlHelper,
        GoogleClient $googleClient
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->urlHelper = $urlHelper;
        $this->serverUrlHelper = $serverUrlHelper;
        $this->googleClient = $googleClient;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $callbackUrl = $this->serverUrlHelper->generate($this->urlHelper->generate('user.sign-in.google.callback'));

        $this->googleClient->setRedirectUri($callbackUrl);

        $this->googleClient->addScope(GoogleOauth2::USERINFO_PROFILE);
        $this->googleClient->addScope(GoogleOauth2::USERINFO_EMAIL);

        $refererLink = new Uri($request->getHeader('referer')[0]);

        if ($refererLink->getHost() === $request->getUri()->getHost()) {
            $sessionSegment = $this->session->getSegment(GoogleClient::class);
            $sessionSegment->set(AbstractSocialSignIn::REFERER, (string)$refererLink);
            $this->session->commit();
        }

        return new RedirectResponse($this->googleClient->createAuthUrl());
    }
}
