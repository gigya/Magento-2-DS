<?php

namespace Gigya\GigyaDS\Model;

use Gigya\GigyaDS\Api\GigyaDSServiceInterface;
use Gigya\GigyaDS\Helper\GigyaDSSyncHelper;

class GigyaDSService implements GigyaDSServiceInterface
{
    public $gigyaSyncHelper;

    public function __construct(
        GigyaDSSyncHelper $gigyaDSSyncHelper
    )
    {
        $this->gigyaSyncHelper = $gigyaDSSyncHelper;
    }

    public function get($uid)
    {

    }

    public function search($uid)
    {

    }

    public function retrieveData($uid)
    {
        $data = $this->gigyaSyncHelper->getDSDataFromGigya();
        $dsData = [];

        foreach ($data as $key => $value) {
            if (strpos($key, "ds.") === 0) {
                $dsData[$key] = $value;
            }
        }
    }

}