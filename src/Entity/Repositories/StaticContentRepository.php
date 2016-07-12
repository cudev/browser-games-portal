<?php

namespace Ludos\Entity\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class StaticContentRepository extends EntityRepository
{
    public function findAllAsArray()
    {
        $query = $this->createQueryBuilder('sc')
            ->getQuery();

        return $query->getResult(Query::HYDRATE_ARRAY);
    }

    public function findAllWithLocales()
    {
        $query = $this->createQueryBuilder('sc')
            ->select(['sc', 'l'])
            ->leftJoin('sc.locale', 'l')
            ->getQuery();

        return $query->getResult(Query::HYDRATE_ARRAY);
    }
}
