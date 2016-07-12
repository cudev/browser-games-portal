<?php

namespace Ludos\Crawling\Lock;

interface LockInterface
{
    public function acquire();
    public function release();
    public function isLocked();
}
