<?php

namespace Ludos\Asset;

use Symfony\Component\Asset\PathPackage;

class HashedPackage extends PathPackage
{
    public function getUrl($path)
    {
        if ($this->isAbsoluteUrl($path)) {
            return $path;
        }

        $hashedPath = static::getSubdirectories($path);

        return $this->getBasePath() . $hashedPath . ltrim($this->getVersionStrategy()->applyVersion($path), '/');
    }

    public static function getSubdirectories($path)
    {
        return $path[0] . $path[1] . '/' . $path[2] . $path[3] . '/';
    }
}
