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
