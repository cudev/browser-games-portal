<?php

namespace Ludos\Action;

use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Google_Client as GoogleClient;
use Google_Service_Oauth2 as GoogleOauth2;
use Ludos\Entity\SocialNetworkConnection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;

class SignInGoogleCallback extends AbstractSocialSignIn
{
    protected $urlHelper;
    protected $serverUrlHelper;
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
        $this->serverUrlHelper = $serverUrlHelper;
        $this->urlHelper = $urlHelper;
        $this->googleClient = $googleClient;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $callbackUrl = $this->serverUrlHelper->generate($this->urlHelper->generate('user.sign-in.google.callback'));

        $this->googleClient->setRedirectUri($callbackUrl);
        $this->googleClient->fetchAccessTokenWithAuthCode($request->getQueryParams()['code']);

        $oauth2 = new GoogleOauth2($this->googleClient);
        $userInfo = $oauth2->userinfo->get();

        if ($userInfo !== null && $userInfo->getEmail()) {
            $this->sign(
                $userInfo->getEmail(),
                $userInfo->getName(),
                $userInfo->getId(),
                SocialNetworkConnection::GOOGLE
            );
        }

        $sessionSegment = $this->session->getSegment(GoogleClient::class);
        $redirectLink = $sessionSegment->get(AbstractSocialSignIn::REFERER, $this->serverUrlHelper->generate('/'));
        $this->session->commit();

        return new RedirectResponse($redirectLink);
    }
}
