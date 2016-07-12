<?php

namespace Ludos\Action;

use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook as FacebookClient;
use Ludos\Entity\SocialNetworkConnection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;

class SignInFacebookCallback extends AbstractSocialSignIn
{
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
        $this->serverUrlHelper = $serverUrlHelper;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $callbackUrl = $this->serverUrlHelper->generate($this->urlHelper->generate('user.sign-in.facebook.callback'));
        $redirectHelper = $this->facebookClient->getRedirectLoginHelper();
        $accessToken = null;

        try {
            $accessToken = $redirectHelper->getAccessToken($callbackUrl);
        } catch (FacebookSDKException $exception) {
            error_log(PHP_EOL . $exception->getMessage());
            return new RedirectResponse('/');
        }

        $graphUser = null;
        if ($accessToken !== null) {
            if (!$accessToken->isLongLived()) {
                // Access token isn't saved because we need it only once to retrieve user data
                $accessToken = $this->facebookClient->getOAuth2Client()->getLongLivedAccessToken($accessToken);
            }

            $facebookResponse = $this->facebookClient->get(
                '/me?fields=id,name,email,picture.width(600).height(600)',
                $accessToken
            );
            $graphUser = $facebookResponse->getGraphUser();
        }

        if ($graphUser !== null && $graphUser->getEmail() !== null) {
            $this->sign(
                $graphUser->getEmail(),
                $graphUser->getName(),
                $graphUser->getId(),
                SocialNetworkConnection::FACEBOOK
            );
        }

        $sessionSegment = $this->session->getSegment(FacebookClient::class);
        $redirectLink = $sessionSegment->get(AbstractSocialSignIn::REFERER, $this->serverUrlHelper->generate('/'));
        $this->session->commit();

        return new RedirectResponse($redirectLink);
    }
}
