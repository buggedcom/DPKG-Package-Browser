<?php

namespace DpkgBrowser\Classes;

/**
 * JsonPayloadData interface provides structure for creating returnable data
 * objects in the JsonPayload.
 *
 * @author Oliver Lillie
 */
interface JsonPayloadDataInterface {

    /**
     * The encode for json function should be used to return the object data
     * in a format that can be json encoded back into the json payload.
     *
     * @author Oliver Lillie
     * @access public
     * @return mixed Typically it should return an array, but can return
     *  anything json encodable.
     */
    public function encodeForJson();

}
