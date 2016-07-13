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
