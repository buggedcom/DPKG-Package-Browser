<?php

namespace DpkgBrowser\Traits;

use OutOfBoundsException;

/**
 * Implements the ArrayAccess interface to provide container functionality.
 * !IMPORTANT: Any class that uses this must `implement ArrayAccess, Iterator,
 * Countable`
 * 
 * @see http://php.net/manual/en/class.arrayaccess.php
 * @author Oliver Lillie
 */
trait ArrayContainer { // implements ArrayAccess, Iterator, Countable

    // $_manifest not declared in trait to allow the class implementing
    // ArrayContainer to declare the property so that annotations can be used
    // to aid PHPStorm.
    // protected $_manifest;

    /**
     * ArrayContainer constructor.
     *
     * @access public
     * @author Oliver Lillie
     *
     * @param array $rows
     */
    public function __construct(array $rows = []) {
        $this->setManifest($rows);
    }

    /**
     * Dumps the existing manifest and sets the current.
     *
     * @author Oliver Lillie
     *
     * @param array $rows
     *
     * @return void
     */
    public function setManifest(array $rows): void {
        $this->_manifest = $rows;
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @see http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void {
        reset($this->_manifest);
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @see http://php.net/manual/en/iterator.current.php
     */
    public function current() {
        return current($this->_manifest);
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @see http://php.net/manual/en/iterator.key.php
     */
    public function key() {
        return key($this->_manifest);
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next() {
        return next($this->_manifest);
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @see http://php.net/manual/en/iterator.valid.php
     */
    public function valid(): bool {
        return $this->current() !== false;
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @see http://php.net/manual/en/countable.count.php
     */
    public function count(): int {
        return count($this->_manifest);
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param $offset
     * @param $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void {
        if ($offset === null) {
            $this->_manifest[] = $value;
        } else {
            $this->_manifest[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool {
        return array_key_exists($offset, $this->_manifest);
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void {
        unset($this->_manifest[$offset]);
    }

    /**
     * @inheritdoc
     * @author Oliver Lillie
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param $offset
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function offsetGet($offset) {
        if (array_key_exists($offset, $this->_manifest) === true) {
            return $this->_manifest[$offset];
        }

        throw new OutOfBoundsException('"' . $offset . '" is not a valid offset of ' . get_class($this) . '.');
    }

    /**
     * Accessor method for getting the raw array contents of the object.
     *
     * @author Oliver Lillie
     * @return mixed
     */
    public function toArray() {
        return $this->_manifest;
    }

}
