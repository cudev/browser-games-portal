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

use Cudev\OrdinaryMail\EmailAddressEncryptor;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;

class EmailConfirm
{
    private $entityManager;
    private $urlHelper;
    private $templateRenderer;
    private $serializer;
    private $emailAddressEncryptor;

    public function __construct(
        EntityManager $entityManager,
        UrlHelper $urlHelper,
        TemplateRendererInterface $templateRenderer,
        Serializer $serializer,
        EmailAddressEncryptor $emailAddressEncryptor
    ) {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
        $this->templateRenderer = $templateRenderer;
        $this->serializer = $serializer;
        $this->emailAddressEncryptor = $emailAddressEncryptor;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $cryptedEmail = urldecode($request->getAttribute('cryptedEmail'));

        if ($cryptedEmail === null) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        $email = $this->emailAddressEncryptor->decrypt($cryptedEmail);

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user === null) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        $user->setEmailConfirmed(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new HtmlResponse($this->templateRenderer->render('app::confirmed'));
    }
}
