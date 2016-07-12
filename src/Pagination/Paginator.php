<?php

namespace Ludos\Pagination;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class Paginator extends DoctrinePaginator
{
    private function pagination($current, $total)
    {
        if (!$total) {
            return [null];
        }
        $sequence = 7;

        if ($total < $sequence) {
            return range(1, $total);
        }

        $pagination = [];

        if ($current >= 5 && $current <= $total - 4) {
            $pagination[] = 1;
            $pagination[] = null;
            $pagination = array_merge($pagination, range($current - 1, $current + 1));
            $pagination[] = null;
            $pagination[] = $total;
            return $pagination;
        }

        if ($current <= 5) {
            $pagination = range(1, 5);
            $pagination[] = null;
            $pagination[] = $total;
            return $pagination;
        }

        if ($current >= $total - 4) {
            $pagination[] = 1;
            $pagination[] = null;
            $pagination = array_merge($pagination, range($total - 4, $total));
            return $pagination;
        }
    }

    public function getPaginationArray()
    {
        return $this->pagination($this->getCurrentPage(), $this->getTotalPages());
    }

    public function getTotalPages()
    {
        return (int)ceil($this->count() / $this->getLimit());
    }

    public function getLimit()
    {
        return $this->getQuery()->getMaxResults();
    }

    public function getCurrentPage()
    {
        return $this->getQuery()->getFirstResult() / $this->getLimit() + 1;
    }
}
