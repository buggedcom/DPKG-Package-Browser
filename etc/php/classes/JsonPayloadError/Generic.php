<?php

namespace DpkgBrowser\Classes\JsonPayloadError;

use LogicException;

/**
 * Class Generic
 *
 * A generic json payload error class.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes\JsonPayloadError
 */
class Generic extends _Abstract {

    /**
     * Returns the error message.
     *
     * @author Oliver Lillie
     * @access public
     * @return string
     * @throws \LogicException
     */
    public function getErrorMessage(): string {
        if (isset($this->_arguments[0]) === false) {
            throw new LogicException('The error message must be set as the first argument of the class constructor.');
        }

        return $this->_arguments[0];
    }

    /**
     * Returns the error code.
     *
     * @author Oliver Lillie
     * @access public
     * @return string
     * @throws \LogicException
     */
    public function getErrorCode(): string {
        if (isset($this->_arguments[1]) === false) {
            throw new LogicException('The error code must be set as the second argument of the class constructor.');
        }

        return $this->_arguments[1];
    }

}
