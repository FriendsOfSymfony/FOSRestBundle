<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Routing\Loader;

use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Routing\RouteCollection;

/**
 * RestRouteDirectoryLoader Directory of REST-enabled controllers router loader.
 *
 * @author Chad Sikorra <chad.sikorra@gmail.com>
 */
class RestRouteDirectoryLoader extends RestRouteLoader
{
    /**
     * Loads a Routes collection by parsing Controller method names from a directory of controllers.
     *
     * @param string $path   The path/resource where the controllers are located
     * @param string $type   The resource type
     *
     * @return RouteCollection A RouteCollection instance
     */
    public function load($path, $type = null)
    {
        $dir = $this->locator->locate($path);

        $collection = new RouteCollection();
        $collection->addResource(new DirectoryResource($dir, '/\.php$/'));
        $files = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY));
        usort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
            return (string) $a > (string) $b ? 1 : -1;
        });

        foreach ($files as $file) {
            if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
                continue;
            }

            if ($class = $this->findClass($file)) {
                $refl = new \ReflectionClass($class);
                if ($refl->isAbstract()) {
                    continue;
                }
                list($prefix, $class) = $this->getControllerLocator($class);

                $restCollection = $this->controllerReader->read($refl);
                $restCollection->prependRouteControllersWithPrefix($prefix);
                $restCollection->setDefaultFormat($this->defaultFormat);

                $collection->addCollection($restCollection);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        try {
            $path = $this->locator->locate($resource);
        } catch (\Exception $e) {
            return false;
        }

        return is_string($resource)
            && 'rest' === $type
            && is_dir($path);
    }
}
