<?php
/**
 * Copyright © 2017 clever-age.
 */

namespace Gigya\GigyaDS\Api;

use Gigya\CmsStarterKit\sdk\GSApiException;

/**
 * Interface GigyaDSServiceInterface
 *
 * Proxy to the Gigya service for all operations concerning the Gigya's DS.
 *
 * @package Gigya\GigyaDS\Api
 */
interface GigyaDSServiceInterface
{
    /**
     * Get a Gigya's DS information with get method.
     *
     * @param string $uid
     * @return array
     */
    function get($uid);

    /**
     * Get Gigya's DS information with search method.
     *
     * @param string $uid
     * @return void
     */
    function search($uid);

    function retrieveData($uid);
}