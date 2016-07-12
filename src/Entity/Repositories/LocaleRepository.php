<?php

namespace Ludos\Entity\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Ludos\Entity\Locale;

class LocaleRepository extends EntityRepository
{
    public function getDefault()
    {
        $query = $this->createQueryBuilder('l')
            ->where('l.language = :language')
            ->getQuery()
            ->setParameter('language', Locale::DEFAULT_LANGUAGE);

        return $query->getOneOrNullResult();
    }
}
