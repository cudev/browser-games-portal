<?php

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
