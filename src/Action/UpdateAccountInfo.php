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

use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;
use Ludos\Asset\HashedPackage;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class UpdateAccountInfo
{
    private $entityManager;
    private $templateRenderer;
    private $filesystem;
    private $hashedPackage;
    private $serializer;

    public function __construct(
        TemplateRendererInterface $templateRenderer,
        EntityManager $entityManager,
        Filesystem $filesystem,
        HashedPackage $hashedPackage,
        Serializer $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->templateRenderer = $templateRenderer;
        $this->filesystem = $filesystem;
        $this->hashedPackage = $hashedPackage;
        $this->serializer = $serializer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        $input = json_decode($request->getBody()->getContents(), true);

        $updateAccountValidator = Validator
            ::key('name', Validator::length(3, 30, true))
            ::key('gender', Validator::in([User::MALE, User::FEMALE]));
        try {
            $errors = [];
            $updateAccountValidator->assert($input);
        } catch (NestedValidationException $exception) {
            $exceptionMessages = $exception->findMessages([
                'name.length',
                'gender.in'
            ]);
            foreach ($exceptionMessages as $key => $message) {
                list($field, $constraint) = explode('_', $key);
                $errors[$field][$constraint] = !empty($message);
            }
        }

        $user->setName($input['name'])
            ->setGender($input['gender'])
            ->setBirthday($input['birthday']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => count($errors) === 0,
            'errors' => $errors,
            'data' => ['user' => $this->serializer->normalize($user)]
        ]);
    }
}
