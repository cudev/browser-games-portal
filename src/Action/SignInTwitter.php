<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Cudev Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
