<?php

namespace Ludos\Template\Aggregators;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Tag;
use Ludos\Middleware\DetectSupportedGames;
use Ludos\Template\AbstractAggregator;

class TagsAggregator extends AbstractAggregator
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getTemplateVariables(): array
    {
        return [
            'tags' => new ArrayCollection($this->entityManager->getRepository(Tag::class)->findBy(['enabled' => true])),
            'supportedGameTypes' => $this->request->getAttribute(DetectSupportedGames::class)
        ];
    }
}
