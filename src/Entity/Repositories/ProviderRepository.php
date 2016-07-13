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
