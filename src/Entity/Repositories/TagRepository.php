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

class TagRepository extends EntityRepository
{
    public function findAllWithTranslations()
    {
        $query = $this->createQueryBuilder('test')
            ->select(['t', 'tn', 'l'])
            ->from('\Ludos\Entity\Game\Tag', 't')
            ->leftJoin('t.tagNames', 'tn', Join::WITH)
            ->leftJoin('tn.locale', 'l', Join::WITH)
            ->getQuery();

        $tags = $query->getResult(Query::HYDRATE_ARRAY);

        foreach ($tags as &$tag) {
            $indexedTagNames = [];
            foreach ($tag['tagNames'] as $tagName) {
                $indexedTagNames[$tagName['locale']['language']] = $tagName;
            }
            $tag['tagNames'] = $indexedTagNames;
        }
        return $tags;
    }
}
