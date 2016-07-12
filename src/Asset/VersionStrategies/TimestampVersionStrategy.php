<?php
namespace Ludos\Asset\VersionStrategies;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class TimestampVersionStrategy implements VersionStrategyInterface
{
    protected $root;
    protected $format;

    public function __construct(string $root, string $format = 'v=%d')
    {
        $this->root = $root;
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion($path)
    {
        if (file_exists($this->root . $path)) {
            return sprintf($this->format, filemtime($this->root . $path));
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function applyVersion($path)
    {
        $applied = '';
        if ($path) {
            $version = $this->getVersion($path);
            $version = $version ? '?' . $version : '';
            $applied = $path . $version;
        }
        return $applied;
    }
}
