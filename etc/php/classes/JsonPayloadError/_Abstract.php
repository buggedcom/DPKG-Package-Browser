<?php

namespace DpkgBrowser\Classes\JsonPayloadError;

use DpkgBrowser\Classes\JsonPayloadDataInterface;

/**
 * Class _Abstract
 *
 * JsonPayloadErrorInterface interface provides structure for creating reusable
 * errors and specific codes.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\JsonPayloadError
 */
abstract class _Abstract implements JsonPayloadDataInterface {

    /**
     * A container for all the arguments supplied to the abstract contstructor.
     *
     * @access private
     * @var array
     * @author Oliver Lillie
     */
    protected $_arguments;

    /**
     * JsonPayloadErrorAbstract constructor.
     *
     * @access public
     * @author Oliver Lillie
     */
    public function __construct() {
        $this->_arguments = func_get_args();
    }

    /**
     * Returns the error message.
     *
     * @author Oliver Lillie
     * @access public
     * @return string
     */
    abstract public function getErrorMessage(): string;

    /**
     * Returns the error code.
     *
     * @author Oliver Lillie
     * @access public
     * @return string
     */
    abstract public function getErrorCode(): string;

    /**
     * The encode for json function should be used to return the object data
     * in a format that can be json encoded back into the json payload.
     *
     * @author Oliver Lillie
     * @access public
     * @return array
     */
    public function encodeForJson(): array {
        return [
            'message' => $this->getErrorMessage(),
            'code'    => $this->getErrorCode(),
        ];
    }

}
