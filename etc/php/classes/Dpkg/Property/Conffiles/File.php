<?php

namespace DpkgBrowser\Classes\Dpkg\Property\Conffiles;

use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\JsonPayloadDataInterface;
use DpkgBrowser\Classes\AccessibleProperties;

/**
 * Class Contact
 *
 * The base property class that is used for package properties.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg\Property\Conffiles
 * @property string file
 * @property string hash
 */
class File extends AccessibleProperties implements JsonPayloadDataInterface {

    /**
     * The private property for the public accessor of "file".
     *
     * @type string|null
     * @author Oliver Lillie
     */
    protected $_file;

    /**
     * The private property for the public accessor of "hash".
     *
     * @type string
     * @author Oliver Lillie
     */
    protected $_hash;

    /**
     * Property constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param string $file
     * @param string $hash
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $file, string $hash) {
        $this->_setAccessibleProperties(
            [
                'file',
                'hash',
            ]
        );

        $this->file = $file;
        $this->hash = $hash;
    }

    /**
     * The setter for setting the "file" value.
     *
     * @author Oliver Lillie
     *
     * @param string $file Must be a non-empty string value.
     *
     * @return \DpkgBrowser\Classes\Dpkg\Property\Conffiles\File
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setFile(string $file): self {
        Args::v(
            $file, Args::notEmpty()
        );

        $this->_file = $file;

        return $this;
    }

    /**
     * The setter for setting the "file" value.
     *
     * @author Oliver Lillie
     *
     * @param string $hash Must be a non-empty string value.
     *
     * @return \DpkgBrowser\Classes\Dpkg\Property\Conffiles\File
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setHash(string $hash): self {
        Args::v(
            $hash, Args::notEmpty()
        );

        $this->_hash = $hash;

        return $this;
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @return mixed|string
     */
    public function encodeForJson() {
        return [
            'file' => $this->_file,
            'hash' => $this->_hash,
        ];
    }

}