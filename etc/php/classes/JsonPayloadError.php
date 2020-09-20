<?php

namespace DpkgBrowser\Classes;

use DpkgBrowser\Classes\JsonPayload;
use DpkgBrowser\Classes\JsonPayloadError\Generic;

/**
 * Class JsonPayloadError
 *
 * Creates an instance of JsonPayload that is given an error with a specific
 * error message and error code.
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes
 */
class JsonPayloadError extends JsonPayload {

    /**
     * JsonPayloadError constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param string $error_message The public message of the error.
     * @param string $error_code The associated error code of the message.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $error_message, string $error_code) {
        parent::__construct();

        $this->error = new Generic($error_message, $error_code);
    }

}