<?php

namespace Ludos\Action;

use Abraham\TwitterOAuth\TwitterOAuth as TwitterClient;
use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;

class SignInTwitter
{
    protected $entityManager;
    protected $session;
    protected $serverUrlHelper;
    protected $urlHelper;
    protected $twitterClient;

    public function __construct(
        EntityManager $entityManager,
        Session $session,
        ServerUrlHelper $serverUrlHelper,
        UrlHelper $urlHelper,
        TwitterClient $twitterClient
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->urlHelper = $urlHelper;
        $this->serverUrlHelper = $serverUrlHelper;
        $this->twitterClient = $twitterClient;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $callbackUrl = $this->serverUrlHelper->generate($this->urlHelper->generate('user.sign-in.twitter.callback'));

        $twitterRequestParameters = ['oauth_callback' => $callbackUrl, 'grant_type' => 'client_credentials'];
        $requestToken = $this->twitterClient->oauth('/oauth/request_token', $twitterRequestParameters);

        $sessionSegment = $this->session->getSegment(TwitterClient::class);
        $sessionSegment->set('request_token', $requestToken);

        $refererLink = new Uri($request->getHeader('referer')[0]);

        if ($refererLink->getHost() === $request->getUri()->getHost()) {
            $sessionSegment = $this->session->getSegment(TwitterClient::class);
            $sessionSegment->set(AbstractSocialSignIn::REFERER, (string)$refererLink);
        }

        $this->session->commit();

        return new RedirectResponse($this->twitterClient->url('oauth/authorize', $requestToken));
    }
}
