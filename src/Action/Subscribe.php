<?php

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
