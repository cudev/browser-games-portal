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
