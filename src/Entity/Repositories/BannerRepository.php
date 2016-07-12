<?php

namespace Ludos\Entity\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Ludos\Entity\Game\Tag;

class BannerRepository extends EntityRepository
{
    public function findOneByTag(Tag $tag)
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->select('b')
            ->leftJoin('b.game', 'g')
            ->leftJoin('g.tags', 't')
            ->where('t.id = :tagId')
            ->andWhere('b.enabled = 1')
            ->orderBy('b.priority', 'DESC')
            ->setMaxResults(1)
            ->setParameter('tagId', $tag->getId());
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findTop()
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->select('b')
            ->where('b.enabled = 1')
            ->orderBy('b.priority', 'DESC')
            ->setMaxResults(1);
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
