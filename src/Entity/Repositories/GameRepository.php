<?php

namespace Ludos\Entity\Repositories;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Ludos\Entity\Game\Description;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Game\Tag;
use Ludos\Entity\Locale;
use Ludos\Entity\User;
use Ludos\Pagination\Paginator;

class GameRepository extends EntityRepository
{
    const SORT_NEW = 'new';
    const SORT_TOP = 'top';
    const SORT_POPULAR = 'popular';
    const SORT_DISCUSSED = 'discussed';

    public function findAllWithTranslations($page = 1, $limit = 10)
    {

        $queryBuilder = $this->createQueryBuilder('g')
            ->select('g')
            ->from(Game::class, 'g')
            ->leftJoin('g.descriptions', 'd', Join::WITH);

        $query = $queryBuilder->getQuery();
        $query->setHydrationMode(Query::HYDRATE_ARRAY);
        $games = $query->getResult();

        return array_slice($games, ($page - 1) * $limit, $limit);
    }

    public function paginateActiveByTag(Tag $tag = null, array $types = null, $order = null, $page = 1, $limit = 24)
    {
        $queryBuilder = $this->createQueryBuilder('g')
            ->select('g')
            ->where('g.enabled = 1')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($tag !== null) {
            $queryBuilder->leftJoin('g.tags', 't')
                ->andWhere('t.id = :tagId')
                ->setParameter('tagId', $tag->getId());
        }

        if ($types !== null) {
            $queryBuilder->andWhere('g.type IN (:types)')->setParameter('types', $types);
        }

        switch ($order) {
            case self::SORT_NEW:
                $queryBuilder->orderBy('g.createdAt', 'DESC');
                break;
            case self::SORT_TOP:
                $queryBuilder
                    ->addSelect('AVG(r.rating) AS HIDDEN averageRating')
                    ->leftJoin('g.ratings', 'r', Join::WITH)
                    ->groupBy('g.id')
                    ->addOrderBy('averageRating', 'DESC');
                break;
            case self::SORT_POPULAR:
                $queryBuilder->orderBy('g.plays', 'DESC');
                break;
            case self::SORT_DISCUSSED:
                $queryBuilder
                    ->addSelect('SUM(c) AS HIDDEN commentsTotal')
                    ->leftJoin('g.comments', 'c', Join::WITH)
                    ->groupBy('g.id')
                    ->addOrderBy('commentsTotal', 'DESC');
                break;
        }

        $queryBuilder->addOrderBy('g.name', 'ASC');

        return new Paginator($queryBuilder->getQuery());
    }

    public function findActiveByTag(Tag $tag = null, array $types = null, $order = null, $page = 1, $limit = 24)
    {
        return iterator_to_array($this->paginateActiveByTag($tag, $types, $order, $page, $limit));
    }

    public function findTop(array $types = null, $page = 1, $limit = 6)
    {
        return $this->findActiveByTag(null, $types, self::SORT_TOP, $page, $limit);
    }

    public function findNew(array $types = null, $page = 1, $limit = 6)
    {
        return $this->findActiveByTag(null, $types, self::SORT_NEW, $page, $limit);
    }

    public function findPopular(array $types = null, $page = 1, $limit = 6)
    {
        return $this->findActiveByTag(null, $types, self::SORT_POPULAR, $page, $limit);
    }

    public function findDiscussed(array $types = null, $page = 1, $limit = 6)
    {
        return $this->findActiveByTag(null, $types, self::SORT_DISCUSSED, $page, $limit);
    }

    public function countAll()
    {
        return $this->createQueryBuilder('g')
            ->select('count(g.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countEnabled()
    {
        return $this->createQueryBuilder('g')
            ->select('count(g.id)')
            ->where('g.enabled = 1')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countWithoutDescription()
    {
        $resultSetMapping = new ResultSetMappingBuilder($this->_em);
        $resultSetMapping->addScalarResult('descriptionless', 'descriptionless');
        $resultSetMapping->addScalarResult('language', 'language');

        $statement = <<<SQL
            SELECT
                COUNT(g.id) as descriptionless, l.language
            FROM (games AS g, locales AS l)
                    LEFT JOIN
                game_descriptions AS gd ON g.id = gd.game_id
                    AND l.id = gd.locale_id
            WHERE gd.translation IS NULL OR gd.translation = '' AND g.enabled = 1
            GROUP BY l.id;
SQL;

        $query = $this->_em->createNativeQuery($statement, $resultSetMapping);

        return $query->getResult();
    }

    public function searchByName($name = null, array $unlocalised = [], $limit = null, $offset = null)
    {
        $resultSetMapping = new ResultSetMappingBuilder($this->_em);
        $resultSetMapping->addRootEntityFromClassMetadata(Game::class, 'g');

        $statement = $this->makeSearchStatement($name, $unlocalised, $limit, $offset);

        $query = $this->_em->createNativeQuery($statement, $resultSetMapping);
        $query->setParameter('name', "%{$name}%");

        return $query->getResult();
    }

    public function countSearchByName($name = null, array $unlocalised = [])
    {
        $resultSetMapping = new ResultSetMappingBuilder($this->_em);
        $resultSetMapping->addScalarResult('total', 'total');

        $statement = $this->makeSearchStatement($name, $unlocalised);

        $countStatement = <<<SQL
            SELECT COUNT(*) AS total FROM ({$statement}) AS search;
SQL;
        $query = $this->_em->createNativeQuery($countStatement, $resultSetMapping);
        $query->setParameter('name', "%{$name}%");

        return $query->getSingleScalarResult();
    }

    private function makeSearchStatement(
        $name = null,
        array $unlocalised = [],
        $limit = null,
        $offset = null
    ) {
        $limitStatement = '';
        if ($limit !== null && is_numeric($limit)) {
            $limitStatement = 'LIMIT ' . $limit;
        }
        $offsetStatement = '';
        if ($offset !== null && is_numeric($offset)) {
            $offsetStatement = 'OFFSET ' . $offset;
        }

        $whereStatements = [];
        if (count($unlocalised) !== 0) {
            $whereStatements[] = '((gd.translation IS NULL OR gd.translation = \'\') AND l.id IN ('
                . implode(', ', $unlocalised)
                . '))';
        }
        if ($name !== null && ctype_alnum(str_replace(' ', '', $name))) {
            $whereStatements[] = 'g.name LIKE :name';
        }

        $whereStatement = '';
        if (count($whereStatements) !== 0) {
            $whereStatement = 'WHERE ' . implode(' AND ', $whereStatements);
        }

        $statement = <<<SQL
            SELECT g.* FROM (games AS g, locales AS l)
              LEFT JOIN game_descriptions AS gd ON g.id = gd.game_id AND l.id = gd.locale_id
            {$whereStatement}
            GROUP BY g.id
            {$limitStatement}
            {$offsetStatement}
SQL;
        return $statement;
    }

    public function getAll($limit = null, $offset = null)
    {
        return $this->findBy([], [], $limit, $offset);
    }

    public function getRecommendedForUser(User $user, $limit = 6)
    {
        $recommendedGames = new ArrayCollection();
        $sourceGames = $user->hasBookmarkedGames() ? $user->getBookmarkedGames() : $user->getPlayedGames();

        foreach ($sourceGames as $bookmarkedGame) {
            /** @var Tag $tag */
            $tag = $bookmarkedGame->getTags()->first();
            if ($tag !== null) {
                foreach ($tag->getGames() as $game) {
                    if ($recommendedGames->contains($game)) {
                        continue;
                    }
                    $recommendedGames->add($game);
                }
            }
        }

        if ($recommendedGames->count() === 0) {
            $recommendedGames = new ArrayCollection($this->findPopular());
        } else {
            $iterator = $recommendedGames->getIterator();
            $iterator->uasort(function (Game $a, Game $b) {
                return $a->getAverageRating() <=> $b->getAverageRating();
            });
            $recommendedGames = new ArrayCollection(array_slice(iterator_to_array($iterator), 0, $limit));
        }

        return $recommendedGames;
    }
}
