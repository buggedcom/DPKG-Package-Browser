<?php

namespace DpkgBrowser\Classes;

/**
 * Class Header
 *
 * @author Oliver Lillie
 * @package DpkgBrowser\Classes
 */
class Header {
    /**
     * Sends cache headers so the page/content is never cached within the
     * browser cache.
     *
     * @access public
     * @return void
     * @author Oliver Lillie
     */
    public static function noCache(): void {
        header('Expires: Tue, 25 Mar 2003 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
    }

    /**
     * Sends access control headers.
     *
     * @author Oliver Lillie
     * @return void
     */
    public static function accessControl(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
            header('Access-Control-Allow-Headers: token, Content-Type');
            header('Access-Control-Max-Age: 1728000');
            header('Content-Length: 0');
            header('Content-Type: text/plain');
            exit;
        }

        header('Access-Control-Allow-Origin: *');
    }

}
