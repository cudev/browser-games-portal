<?php

namespace Ludos\Iterator;

use Closure;
use IteratorIterator;
use Traversable;

class MapIterator extends IteratorIterator
{
    /** @var Closure Callback */
    protected $callback;

    /**
     * @param Traversable $iterator collection
     * @param Closure $callback applied to each element
     */
    public function __construct(Traversable $iterator, Closure $callback)
    {
        parent::__construct($iterator);
        $this->callback = $callback;
    }

    /** {@inheritdoc} */
    public function current()
    {
        return ($this->callback)(parent::current());
    }
}
