<?php

namespace Ludos\Action;

use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Facebook\Facebook as FacebookClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;

class SignInFacebook
{
    protected $entityManager;
    protected $session;
    protected $facebookClient;
    protected $serverUrlHelper;
    protected $urlHelper;

    public function __construct(
        EntityManager $entityManager,
        Session $session,
        FacebookClient $facebookClient,
        ServerUrlHelper $serverUrlHelper,
        UrlHelper $urlHelper
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->facebookClient = $facebookClient;
        $this->urlHelper = $urlHelper;
        $this->serverUrlHelper = $serverUrlHelper;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $redirectHelper = $this->facebookClient->getRedirectLoginHelper();
        $permissions = ['email'];

        $callbackUrl = $this->serverUrlHelper->generate($this->urlHelper->generate('user.sign-in.facebook.callback'));

        $loginUrl = $redirectHelper->getLoginUrl($callbackUrl, $permissions);

        $refererLink = new Uri($request->getHeader('referer')[0]);

        if ($refererLink->getHost() === $request->getUri()->getHost()) {
            $sessionSegment = $this->session->getSegment(FacebookClient::class);
            $sessionSegment->set(AbstractSocialSignIn::REFERER, (string)$refererLink);
            $this->session->commit();
        }

        return new RedirectResponse($loginUrl);
    }
}
