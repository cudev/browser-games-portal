<?php

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
