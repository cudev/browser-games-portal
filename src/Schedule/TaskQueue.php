<?php

namespace Ludos\Schedule;

use SplDoublyLinkedList;
use SplQueue;

class TaskQueue
{
    /** @var SplQueue */
    private $queue;

    public function __construct()
    {
        $this->queue = new SplQueue();
        $this->queue->setIteratorMode(SplDoublyLinkedList::IT_MODE_DELETE);
    }

    public function enqueue(callable $task)
    {
        $this->queue->enqueue($task);
    }

    public function perform()
    {
        for ($this->queue->rewind(); $this->queue->valid(); $this->queue->next()) {
            /** @var callable $task */
            $task = $this->queue->current();
            $task();
        }
    }
}
