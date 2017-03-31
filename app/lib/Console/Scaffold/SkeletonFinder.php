<?php

namespace Honeylex\Console\Scaffold;

use Symfony\Component\Finder\Finder;

/**
 * Finder that searches for skeletons in the given or default locations.
 */
class SkeletonFinder
{
    protected $lookupPaths;

    /**
     * Finds a skeleton by name. Initializes itself with the honeybee
     * default skeleton lookup paths if none are given.
     *
     * @param array $lookupPaths folders that contain skeleton directories
     */
    public function __construct(array $lookupPaths)
    {
        $this->lookupPaths = $lookupPaths;
    }

    /**
     * Returns an array of found skeletons with that name in configured or
     * given locations.
     *
     * @return Symfony\Component\Finder\SplFileInfo instance of the first found skeleton folder
     */
    public function findByName($name, array $locations = [])
    {
        if (empty($locations)) {
            $locations = $this->lookupPaths;
        }

        $finder = new Finder;
        $finder->directories()->depth(0)->name($name)->in($locations);

        $skeletons = iterator_to_array($finder, true);

        return reset($skeletons);
    }

    /**
     * Returns an array of all found skeletons in the configured or given
     * locations.
     *
     * @return array with Symfony\Component\Finder\SplFileInfo instances of skeleton folders
     */
    public function findAll(array $locations = [])
    {
        if (empty($locations)) {
            $locations = $this->lookupPaths;
        }

        $finder = new Finder;
        $finder->directories()->depth(0)->sortByName()->in($locations);

        return iterator_to_array($finder, true);
    }
}
