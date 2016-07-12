<?php

namespace Ludos\Action;

use Abraham\TwitterOAuth\TwitterOAuth as TwitterClient;
use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\SocialNetworkConnection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;

class SignInTwitterCallback extends AbstractSocialSignIn
{
    protected $urlHelper;
    protected $serverUrlHelper;
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
        $this->serverUrlHelper = $serverUrlHelper;
        $this->urlHelper = $urlHelper;
        $this->twitterClient = $twitterClient;
    }

    /**
     * @link https://dev.twitter.com/rest/reference/get/account/verify_credentials
     * @link https://twitteroauth.com
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return RedirectResponse
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $twitterSessionSegment = $this->session->getSegment(TwitterClient::class);
        $requestToken = $twitterSessionSegment->get('request_token');

        $this->twitterClient->setOauthToken($requestToken['oauth_token'], $requestToken['oauth_token_secret']);

        $accessToken = $this->twitterClient->oauth(
            'oauth/access_token',
            ['oauth_verifier' => $request->getQueryParams()['oauth_verifier']]
        );

        $this->twitterClient->setOauthToken($accessToken['oauth_token'], $accessToken['oauth_token_secret']);

        /**
         * Need to use strings in parameters instead of boolean values
         * @link https://github.com/abraham/twitteroauth/issues/364#issuecomment-191773358
         * @var stdClass $userInfo
         */
        $userInfo = $this->twitterClient->get(
            'account/verify_credentials',
            ['include_email' => 'true', 'skip_status' => 'true']
        );

        if ($userInfo !== null && property_exists($userInfo, 'email')) {
            $this->sign(
                $userInfo->email,
                $userInfo->name,
                $userInfo->id,
                SocialNetworkConnection::TWITTER
            );
        }

        $sessionSegment = $this->session->getSegment(TwitterClient::class);
        $redirectLink = $sessionSegment->get(AbstractSocialSignIn::REFERER, $this->serverUrlHelper->generate('/'));
        $this->session->commit();

        return new RedirectResponse($redirectLink);
    }
}
