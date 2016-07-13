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
use Ludos\Entity\Role;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use Zend\Diactoros\Response\JsonResponse;

class Subscribe
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $parsedRequestBody = json_decode($request->getBody()->getContents(), true) ?? [];
        $errors = $this->validateInput($parsedRequestBody);
        $success = count($errors) === 0;

        if ($success) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $parsedRequestBody['email']
            ]);

            if ($user === null) {
                $user = new User();
                $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => Role::SUBSCRIBER]);

                $user->setEmail($parsedRequestBody['email'])
                    ->setName($parsedRequestBody['email'])
                    ->setRole($role);
            }

            // Check if subscribed to prevent querying database if user exists
            if (!$user->isSubscribed()) {
                $user->setSubscribed(true);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        }

        return new JsonResponse(['success' => $success, 'errors' => $errors]);
    }

    private function validateInput(array $input)
    {

        $subscribeValidator = Validator::key('email', Validator::email()->notEmpty());

        $errors = [];

        try {
            $subscribeValidator->assert($input);
        } catch (NestedValidationException $exception) {
            $exceptionMessages = $exception->findMessages([
                'email.notEmpty',
                'email.email'
            ]);
            $errors = [];
            foreach ($exceptionMessages as $key => $message) {
                list($field, $constraint) = explode('_', $key);
                $errors[$field][$constraint] = !empty($message); // we can safely empty() here
            }
        }
        return $errors;
    }
}
