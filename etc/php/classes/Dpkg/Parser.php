<?php

namespace DpkgBrowser\Classes\Dpkg;

use ArrayAccess;
use Countable;
use Iterator;
use DpkgBrowser\Classes\AccessibleProperties;
use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\Cache;
use DpkgBrowser\Traits\ArrayContainer;

/**
 * Class Parser
 *
 * The class responsible for parsing the dpkg/status file.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg
 * @property $path string
 */
class Parser extends AccessibleProperties implements ArrayAccess, Iterator, Countable {

    use ArrayContainer;

    /**
     * The data container from ArrayContainer.
     *
     * @type \DpkgBrowser\Classes\Dpkg\Package[]
     * @author Oliver Lillie
     */
    protected $_manifest;

    /**
     * The path to the dpkg status file.
     *
     * @type string
     * @author Oliver Lillie
     */
    private $_path;

    /**
     * Parser constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param string $path The path to the file that is to be parsed.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(string $path) {
        $this->_setAccessibleProperties(
            [
                'path',
            ]
        );

        $this->path = $path;

        $this->parse();
    }

    /**
     * Sets the path of the dpkg status file (the "path" property).
     *
     * @author Oliver Lillie
     *
     * @param string $path The file path that must lead to a readable file.
     *
     * @return self
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setPath(string $path): self {
        Args::v(
            $path, Args::file()->readable()
        );

        $this->_path = $path;

        return $this;
    }

    /**
     * Checks for a md5 file hash match from the cached version of the previous
     * parse. If the cache is valid then it is set into the parsers manifest and
     * true is returned so that ::parse will know that it has a cache and no
     * longer needs to fire.
     *
     * @author Oliver Lillie
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \Stash\Exception\RuntimeException
     */
    private function _loadParsedCache(): bool {
        // check the md5 file hash of a cached data set to see if the dpkg
        // status has changed. If it has not changed and we have a serialized
        // version of the manifest then we will use the cached value instead of
        // reparsing the file again for performance.
        $pool = Cache::getPool();
        $item = $pool->getItem('dpkg/list/hash');
        $hash = $item->get();
        if ($item->isMiss() === false) {
            if(md5_file($this->_path) === $hash) {
                $item = $pool->getItem('dpkg/list/manifest');
                $manifest = $item->get();
                if ($item->isMiss() === false) {
                    $this->setManifest($manifest);

                    return true;
                }
            }

            // if the caches md5 hash is not the same as the current then we
            // remove all the cached data under dpkg since everything may have
            // to be updated
            Cache::getPool()->deleteItem('dpkg/');
        }

        return false;
    }

    /**
     * Kicks of the parsing of the status file.
     *
     * @author Oliver Lillie
     * @return self
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function parse(): self {
        // check to see if the manifest already exists or has a cached version
        // available. If there is, don't do any further parsing.
        if (empty($this->_manifest) === false || $this->_loadParsedCache() === true) {
            return $this;
        }

        $pointer = fopen($this->_path, 'r');
        if ($pointer === false) {
            throw new \RuntimeException('Unable to open dkpg status file at path: ' . $this->path);
        }

        $packages = [];
        while ($line = stream_get_line($pointer, 1024 * 1024, "\n\n")) {
            $packages[] = new Package($line);
        }
        fclose($pointer);

        $this->_setManifestAndCache($packages);

        return $this;
    }

    /**
     * Sets the parsers manifest and then sets various caches so next time the
     * parser runs it doesn't have to do anything aside from load the cache.
     *
     * @author Oliver Lillie
     *
     * @param Package[] $manifest
     *
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Stash\Exception\RuntimeException
     */
    private function _setManifestAndCache($manifest): void {
        $this->setManifest($manifest);

        $pool = Cache::getPool();

        $item = $pool->getItem('dpkg/hash');
        $item->set(md5_file($this->_path));
        $pool->save($item);

        $item = $pool->getItem('dpkg/manifest');
        $item->set($manifest);
        $pool->save($item);
    }

    /**
     * Returns a simply array of names of the packages contained in the
     * manifest.
     *
     * @author Oliver Lillie
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Stash\Exception\RuntimeException
     */
    public function getPackageList(): array {
        $pool = Cache::getPool();
        $item = $pool->getItem('dpkg/list');

        $cache = $item->get();
        if($item->isHit() === true) {
            return $cache;
        }

        $cache = [];
        foreach ($this->_manifest as $package) {
            $cache[] = $package->package->value;
        }

        $item->set($cache);
        $pool->save($item);

        return $cache;
    }

}