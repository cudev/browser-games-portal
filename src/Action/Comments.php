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
use Cudev\OrdinaryMail\CredentialsInterface;
use Cudev\OrdinaryMail\Letter;
use Cudev\OrdinaryMail\Mailer;
use Doctrine\ORM\EntityManager;
use Hashids\Hashids;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Serializer\JsonApiSerializer;
use Ludos\Entity\Comment;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Locale;
use Ludos\Entity\User;
use Ludos\Schedule\TaskQueue;
use Ludos\Serialization\Normalizers\CommentNormalizer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Asset\UrlPackage;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class Comments
{
    private $entityManager;
    private $session;
    private $urlPackage;
    private $hashids;
    private $taskQueue;
    private $mailer;
    private $templateRenderer;

    public function __construct(
        EntityManager $entityManager,
        Session $session,
        UrlPackage $urlPackage,
        Hashids $hashids,
        TaskQueue $taskQueue,
        Mailer $mailer,
        TemplateRendererInterface $templateRenderer
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->urlPackage = $urlPackage;
        $this->hashids = $hashids;
        $this->taskQueue = $taskQueue;
        $this->mailer = $mailer;
        $this->templateRenderer = $templateRenderer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var Game|null $game */
        $gameSlug = $request->getAttribute('gameSlug');

        /** @var User|null $user */
        $user = $request->getAttribute('user');

        $game = null;
        if ($gameSlug !== null) {
            $game = $this->entityManager->getRepository(Game::class)->findOneBy(['slug' => $gameSlug]);
        }
        if ($game === null) {
            // return early if game is not found
            return new JsonResponse(['success' => false], 400);
        }

        $parsedRequestBody = json_decode($request->getBody(), true);

        $normalizer = new CommentNormalizer($this->urlPackage, $this->hashids);

        switch ($request->getMethod()) {
            case 'GET':
                /** @var Comment[] $comments */
                $comments = $this->entityManager
                    ->getRepository(Comment::class)
                    ->findBy([
                        'game' => $game->getId(),
                        'locale' => $request->getAttribute(Locale::class)->getId()
                    ]);

                $commentsNormalized = [];
                foreach ($comments as $comment) {
                    $commentsNormalized[] = $normalizer->normalize($comment, CommentNormalizer::SAFE_FORMAT);
                }
                return new JsonResponse(['success' => true, 'data' => $commentsNormalized]);
            case 'POST':
                if ($user === null) {
                    return new JsonResponse(['success' => false], 401);
                }
                $comment = new Comment();
                $comment->setGame($game)
                    ->setLocale($request->getAttribute(Locale::class))
                    ->setUser($user)
                    ->setBody($parsedRequestBody['body']);

                $this->entityManager->persist($comment);
                $this->entityManager->flush();

                $this->notify($comment);

                return new JsonResponse([
                    'success' => true,
                    'data' => $normalizer->normalize($comment, CommentNormalizer::SAFE_FORMAT)
                ]);
            case 'PATCH':
                if ($user === null) {
                    return new JsonResponse(['success' => false], 401);
                }

                /** @var Comment $comment */
                $comment = $this->entityManager
                    ->getRepository(Comment::class)
                    ->find($this->hashids->decode($parsedRequestBody['id'])[0]);

                if (empty($parsedRequestBody['body']) || $user->getId() !== $comment->getUser()->getId()) {
                    return new JsonResponse(['success' => false], 400);
                }

                $comment->setBody($parsedRequestBody['body']);
                $this->entityManager->persist($comment);
                $this->entityManager->flush();

                return new JsonResponse([
                    'success' => true,
                    'data' => $normalizer->normalize($comment, CommentNormalizer::SAFE_FORMAT)
                ]);
        }
    }

    private function notify(Comment $comment)
    {
        $task = function () use ($comment) {
            $html = $this->templateRenderer->render('email::notification', ['comment' => $comment]);

            $inliner = new CssToInlineStyles($html);
            $inliner->setUseInlineStylesBlock(true);
            $inliner->setCleanup(true);
            $inliner->setStripOriginalStyleTags(true);

            $body = $inliner->convert($html);

            $letter = new Letter('Comment added', $body);

            $admin = new User();
            $admin->setName($comment->getLocale()->getDomain())->setEmail('info@playgames.cool');

            $letter->setSender($admin)->addRecipient($admin);
            $this->mailer->send($letter);
        };
        $this->taskQueue->enqueue($task);
    }
}
