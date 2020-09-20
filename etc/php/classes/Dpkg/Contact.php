<?php

namespace DpkgBrowser\Classes\Dpkg;

use CaseConverter\CaseString;
use DpkgBrowser\Classes\Args;
use DpkgBrowser\Classes\JsonPayloadDataInterface;
use RuntimeException;
use DpkgBrowser\Classes\AccessibleProperties;

/**
 * Class Contact
 *
 * The base property class that is used for package properties.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\Dpkg
 * @property string display
 * @property string address
 */
class Contact extends AccessibleProperties implements JsonPayloadDataInterface {

    /**
     * The private property for the public accessor of "display".
     *
     * @type string|null
     * @author Oliver Lillie
     */
    protected $_display;

    /**
     * The private property for the public accessor of "address".
     *
     * @type string
     * @author Oliver Lillie
     */
    protected $_address;

    /**
     * Property constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param string $address
     * @param string|null $display
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $address, ?string $display) {
        $this->_setAccessibleProperties(
            [
                'display',
                'address',
            ]
        );

        $this->address = $address;
        $this->display = $display;
    }

    /**
     * The setter for setting the "address" value.
     *
     * @author Oliver Lillie
     *
     * @param string $address Must be a non-empty string value that is an email.
     *
     * @return \DpkgBrowser\Classes\Dpkg\Contact
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setAddress(string $address): self {
        Args::v(
            $address, Args::notEmpty()->email()
        );

        $this->_address = $address;

        return $this;
    }

    /**
     * The setter for setting the "display" value.
     *
     * @author Oliver Lillie
     *
     * @param string|null $display Must either be null or a non-empty camel
     *  case string.
     *
     * @return \DpkgBrowser\Classes\Dpkg\Contact
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     */
    public function setDisplay(?string $display): self {
        Args::v(
            $display, Args::oneOf(
                Args::notEmpty(),
                Args::nullType()
            )
        );

        $this->_display = $display;

        return $this;
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @return mixed|string
     */
    public function encodeForJson() {
        return [
            'address' => $this->_address,
            'display' => $this->_display,
        ];
    }

}