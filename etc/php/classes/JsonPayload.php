<?php

namespace DpkgBrowser\Classes;

use DpkgBrowser\Classes\JsonPayloadError\_Abstract;

/**
 * JsonPayload provides a uniform way of returning json data back to the
 * browser.
 *
 * @author Oliver Lillie
 * @property boolean $status The status property signals the return response
 *  status. It is a boolean value and if true means that the request completed
 *  succesfully.
 * @property mixed $data This is generally an array but can contain any value
 *  that is to be returned by the request. However if it is an object, said
 *  object must implement the JsonPayloadDataInterface to be accepted.
 * @property array $meta Any specifically non requested data that the request
 *  may want to return can be contained in this array. It ideally should be key
 *  => value pairs, however there is no strict structure imposed on the value.
 * @property array $misc Holds miscellaneous data as key=>value pairs.
 * @property _Abstract $error This is the error object to be
 *  returned. Each error object must implement the JsonPayloadErrorInterface to
 *  be set. Also note that by setting an error you automatically are setting
 *  status to false.
 */
class JsonPayload extends AccessibleProperties {

    /**
     * The status property signals the return response status.
     * It is a boolean value and if true means that the request completed
     * succesfully.
     *
     * @author Oliver Lillie
     * @access private
     * @var boolean
     * @default true
     */
    protected $_status;

    /**
     * This is the error object to be returned. Each error object must implement
     * the JsonPayloadErrorInterface to be set. Also not that by setting an
     * error you automatically are setting status to false.
     *
     * @author Oliver Lillie
     * @access private
     * @var object Must implement JsonPayloadErrorInterface
     * @default null
     */
    protected $_error;

    /**
     * This is generally an array but can contain any value that is to be
     * returned by the request. However if it is an object, said object must
     * implement the JsonPayloadDataInterface to be accepted.
     *
     * @author Oliver Lillie
     * @access private
     * @var mixed
     * @default array()
     */
    protected $_data;

    /**
     * Any specifically non requested data that the request may want to return
     * can be contained in this array. It ideally should be key => value pairs,
     * however there is no strict structure imposed on the value.
     *
     * @author Oliver Lillie
     * @access private
     * @var array
     * @default array()
     */
    protected $_meta;

    /**
     * Public constructed of the JsonPayload object.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param mixed $data The main data to be returned by the payload object can
     *  be set here or later on through setting $json->data = $data;
     * @param array $meta Any related meta.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($data = [], array $meta = []) {
        // $data is not validated since it is validated throug the setter
        // setData

        $this->_setAccessibleProperties(
            [
                'status',
                'error',
                'data',
                'meta'
            ]
        );

        $this->status = true;
        $this->error = null;
        $this->data = $data;
        $this->meta = $meta;
    }

    /**
     * Sets the status property as a boolean value.
     *
     * @access public
     *
     * @param boolean $value
     *
     * @return JsonPayload
     * @author Oliver Lillie
     */
    public function setStatus(bool $value): self {
        $this->_status = $value;

        return $this;
    }

    /**
     * Sets the payload error message to be returned.
     *
     * @access public
     *
     * @param null|_Abstract $value Can be a string or null value.
     *
     * @return JsonPayload
     * @author Oliver Lillie
     */
    public function setError(?_Abstract $value): self {
        $this->_error = $value;
        $this->_status = !($value !== null);

        return $this;
    }

    /**
     * Sets the data to be returned in the json payload. The data can be any
     * value that is to be returned but typically should be an array. However if
     * the value is an object, that object must implement the JsonPayloadData
     * interface otherwise the set will fail.
     *
     * @access public
     *
     * @param JsonPayloadDataInterface|array $value
     *
     * @return JsonPayload
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \ReflectionException
     * @author Oliver Lillie
     */
    public function setData($value): self {
        Args::v(
            $value, Args::oneOf(
                Args::objectType()->instance(JsonPayloadDataInterface::class),
                Args::arrayType()
            )
        );

        $this->_data = $value;

        return $this;
    }

    /**
     * Sets the meta value for the return request. If any meta is returned it is
     * suggested that it is in the key=>value pair format, however no strict
     * structure is imposed.
     *
     * @access public
     *
     * @param array $value
     *
     * @return JsonPayload
     * @author Oliver Lillie
     */
    public function setMeta(array $value): self {
        $this->_meta = $value;

        return $this;
    }

    /**
     * Process a value and converts it from object or an array of objects to the
     * encoded for json array.
     *
     * @author Oliver Lillie
     *
     * @param array|object $data
     *
     * @return array|null
     */
    private function _valueToArray($data):? array {
        if (is_object($data) === true) {
            $data = $data->encodeForJson();
        } elseif (is_array($data) === true) {
            $data = $this->_recursiveArrayEncodeForJson($data);
        }
        return $data;
    }

    /**
     * Returns the json array unencoded.
     *
     * @access public
     * @return array
     * @author Oliver Lillie
     */
    public function toArray(): array {
        $json = [
            'status' => $this->_status,
            'error'  => $this->_valueToArray($this->_error),
            'data'   => $this->_valueToArray($this->_data),
            'meta'   => $this->_valueToArray($this->_meta)
        ];

        return $json;
    }

    /**
     * Recurses through an array of objects attempting to encode each object for
     * json.
     *
     * @access public
     * @author: Oliver Lillie
     *
     * @param mixed $data An array of data to encode for json.
     *
     * @return array
     */
    protected function _recursiveArrayEncodeForJson($data): array {
        if (is_array($data) === false) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_object($value) === true && method_exists($value, 'encodeForJson') === true) {
                $data[$key] = $value->encodeForJson();
            } elseif (is_array($value) === true) {
                $data[$key] = $this->_recursiveArrayEncodeForJson($value);
            }
        }

        return $data;
    }

    /**
     * Returns the json encoded JsonPayload string.
     *
     * @see JsonPayload::__toString
     * @access public
     * @return string
     * @author Oliver Lillie
     */
    public function toJson(): string {
        return $this->__toString();
    }

    /**
     * Encodes the JsonPayload into the json encoded string.
     *
     * @access public
     * @return string
     * @author Oliver Lillie
     */
    public function __toString() {
        return (string)json_encode($this->toArray());
    }

    /**
     * Outputs the json payload to the browser.
     *
     * @access public
     * @author: Oliver Lillie
     * @return void
     */
    public function output(): void {
        if (PHP_SAPI !== 'cli') {
            header('Content-type: application/json; charset=utf-8');
        }
        echo $this->toJson();
        exit;
    }

}