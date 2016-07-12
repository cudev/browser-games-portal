<?php

namespace Ludos\Entity\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Ludos\Entity\Provider\Provider;

class ProviderRepository extends EntityRepository
{
    public function findWithStatistics($id = null)
    {

        $queryBuilder = $this->createQueryBuilder('provider');
        $queryBuilder->select('p')
            ->addSelect($queryBuilder->expr()->count('c'))
            ->addSelect($queryBuilder->expr()->count('g'))
            ->from(Provider::class, 'p')
            ->leftJoin('p.categories', 'c', Join::WITH)
            ->leftJoin('p.games', 'g', Join::WITH)
            ;

        if (isset($id)) {
            $queryBuilder->where('p.id = ' . $id);
        }

        $query = $queryBuilder->getQuery();
        $query->setHydrationMode(Query::HYDRATE_ARRAY);
        $provider = $query->getOneOrNullResult();

        if (null !== $provider) {
            $provider[0]['gamesTotal'] = (int)$provider[1];
            $provider[0]['categoriesTotal'] = (int)$provider[2];
        }

        return $provider;
    }
}
