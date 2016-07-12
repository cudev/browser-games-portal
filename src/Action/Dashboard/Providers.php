<?php

namespace Ludos\Action\Dashboard;

use Doctrine\ORM\EntityManager;
use Ludos\Entity\Provider\Provider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Providers extends AbstractResourceAction
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function get(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $id = $request->getAttribute('id');
        if ($id) {
            $provider = $this->entityManager->getRepository(Provider::class)->find($id);
            if (null === $provider) {
                return $next($request, $response->withStatus(404), 'Not found');
            }
        } else {
            $provider = $this->entityManager->getRepository(Provider::class)->findAll();
        }
        return new JsonResponse(['data' => $provider]);
    }
}
