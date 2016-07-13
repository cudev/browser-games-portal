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
