<?php

namespace Ludos\Action;

use Aura\Intl\TranslatorInterface;
use Aura\Intl\TranslatorLocator;
use Aura\Session\Session;
use Cudev\OrdinaryMail\EmailAddressEncryptor;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\Locale;
use Ludos\Entity\Role;
use Ludos\Entity\StaticContent;
use Ludos\Entity\User;
use Cudev\OrdinaryMail\CredentialsInterface;
use Cudev\OrdinaryMail\Letter;
use Cudev\OrdinaryMail\Mailer;
use Ludos\Schedule\TaskQueue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class SignUp
{
    private $entityManager;
    private $session;
    private $mailer;
    private $taskQueue;
    private $translatorLocator;
    private $templateRenderer;
    private $emailAddressEncryptor;

    public function __construct(
        EntityManager $entityManager,
        Session $session,
        Mailer $mailer,
        TaskQueue $taskQueue,
        TemplateRendererInterface $templateRenderer,
        TranslatorLocator $translatorLocator,
        EmailAddressEncryptor $emailAddressEncryptor
    ) {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->mailer = $mailer;
        $this->taskQueue = $taskQueue;
        $this->translatorLocator = $translatorLocator;
        $this->templateRenderer = $templateRenderer;
        $this->emailAddressEncryptor = $emailAddressEncryptor;
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
            $role = $this->entityManager->getRepository(Role::class)->findOneBy([
                'name' => Role::USER
            ]);

            if ($user === null) {
                $user = new User();
                $user->setEmail($parsedRequestBody['email']);
            }

            $user->setRole($role)->setPasswordHash(User::makePasswordHash($parsedRequestBody['password']));

            if (!$user->hasName()) {
                $user->setName($parsedRequestBody['email']);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->session->getSegment(User::class)->set('userId', $user->getId());
            $this->session->commit();

            /** @var Locale $locale */
            $locale = $request->getAttribute(Locale::class);
            $translator = $this->translatorLocator->get(StaticContent::class, $locale->getLanguage());

            $this->enqeueTask($user, $translator, $locale);
        }

        return new JsonResponse(['success' => $success, 'errors' => $errors]);
    }

    private function enqeueTask(User $user, TranslatorInterface $translator, Locale $locale)
    {
        $task = function () use ($user, $translator, $locale) {
            $subject = $translator->translate('email.confirmation.subject', ['domain' => $locale->getDomain()]);

            $html = $this->templateRenderer->render(
                'email::confirmation',
                [
                    'user' => $user,
                    'letterType' => 'confirm',
                    'cryptedEmail' => $this->emailAddressEncryptor->encrypt($user->getEmail())
                ]
            );

            $inliner = new CssToInlineStyles($html);
            $inliner->setUseInlineStylesBlock(true);
            $inliner->setCleanup(true);
            $inliner->setStripOriginalStyleTags(true);

            $body = $inliner->convert($html);

            $letter = new Letter($subject, $body);

            $sender = new class('PlayGames.Cool', 'info@playgames.cool') implements CredentialsInterface
            {
                private $name;
                private $address;

                public function __construct($name, $address)
                {
                    $this->name = $name;
                    $this->address = $address;
                }

                public function getAddress()
                {
                    return $this->address;
                }

                public function getName()
                {
                    return $this->name;
                }
            };

            $letter->setSender($sender)->addRecipient($user);
            $this->mailer->send($letter);
        };
        $this->taskQueue->enqueue($task);
    }

    private function validateInput(array $input)
    {
        $checkUserExistence = function () use ($input) {
            /** @var User $existingUser */
            $existingUser = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    'email' => $input['email']
                ]);
            return $existingUser === null || !$existingUser->hasPasswordHash();
        };

        $signUpValidator = Validator::key('email', Validator::email()->notEmpty()->callback($checkUserExistence))
            ->key('password', Validator::notEmpty()->noWhitespace()->length(6, 50))
            ->key('passwordAgain', Validator::equals($input['password']));

        $errors = [];

        try {
            $signUpValidator->assert($input);
        } catch (NestedValidationException $exception) {
            $exceptionMessages = $exception->findMessages([
                'email.notEmpty',
                'email.email',
                'email.callback' => 'Exists',
                'password.notEmpty',
                'password.noWhitespace',
                'password.length',
                'passwordAgain.equals'
            ]);
            $errors = [];
            foreach ($exceptionMessages as $key => $message) {
                list($field, $constraint) = explode('_', $key);
                $errors[$field][$constraint] = !empty($message);
            }
        }
        return $errors;
    }
}
