<?php

namespace Gigya\GigyaDS\Model;

use Gigya\CmsStarterKit\ds\DsQueryObject;
use Gigya\CmsStarterKit\sdk\GSApiException;
use Gigya\CmsStarterKit\sdk\GSObject;
use Gigya\GigyaDS\Api\GigyaDSServiceInterface;
use Gigya\GigyaDS\Helper\GigyaDSSyncConfigHelper;
use Gigya\GigyaDS\Helper\GigyaDSSyncHelper;
use Gigya\GigyaDS\Model\Config\Source\FetchMethod;
use Gigya\GigyaIM\Helper\GigyaMageHelper;
use Gigya\GigyaIM\Logger\Logger as GigyaLogger;

/**
 * Class GigyaDSService
 *
 * This class is used to make calls to the Gigya DS service
 * It contain all the fonction needed to make a get or a search request
 *
 * @package Gigya\GigyaDS\Model
 */
class GigyaDSService implements GigyaDSServiceInterface
{
    /** @var GigyaDSSyncHelper $gigyaSyncHelper */
    public $gigyaSyncHelper;

    /** @var GigyaDSSyncConfigHelper $gigyaDSSyncConfigHelper */
    public $gigyaDSSyncConfigHelper;

    /** @var GigyaMageHelper $gigyaMageHelper */
    public $gigyaMageHelper;

    /** @var array $_dsData */
    protected $_dsData;

    /** @var DsQueryObject $_dsQueryObject */
    protected $_dsQueryObject;

    /** @var GigyaLogger $_logger */
    protected $_logger;

    /**
     * GigyaDSService constructor.
     * @param GigyaDSSyncHelper $gigyaDSSyncHelper
     * @param GigyaDSSyncConfigHelper $gigyaDSSyncConfigHelper
     * @param GigyaMageHelper $gigyaMageHelper
     * @param GigyaLogger $logger
     */
    public function __construct(
        GigyaDSSyncHelper $gigyaDSSyncHelper,
        GigyaDSSyncConfigHelper $gigyaDSSyncConfigHelper,
        GigyaMageHelper $gigyaMageHelper,
        GigyaLogger $logger
    )
    {
        $this->gigyaSyncHelper = $gigyaDSSyncHelper;
        $this->gigyaDSSyncConfigHelper = $gigyaDSSyncConfigHelper;
        $this->gigyaMageHelper = $gigyaMageHelper;
        $this->_logger = $logger;
    }

    /**
     * Get the DS data
     *
     * @return array
     */
    public function getDSData()
    {
        return $this->_dsData;
    }

    /**
     * Set the DS data
     *
     * @param $dsData
     */
    public function setDSData($dsData)
    {
        $this->_dsData = $dsData;
    }

    /**
     * Get the mapping transformed to make easy request
     *
     * @return array
     */
    public function getMapping()
    {
        return $this->gigyaSyncHelper->updateMappingForRequest($this->getDSData());
    }

    /**
     * Unset previous dsQueryObject and instantiate a new one to clean where parameters
     */
    protected function initializeDsQueryObject()
    {
        if (!empty($this->_dsQueryObject) && is_object($this->_dsQueryObject)) {
            unset($this->_dsQueryObject);
        }
        $this->_dsQueryObject = new DsQueryObject($this->gigyaMageHelper->getGigyaApiHelper());
    }

    /**
     * Method used to call get method from DS api
     *
     * @param string $uid
     * @return array
     */
    public function get($uid)
    {
        $mapping = $this->getMapping();
        $result = ['ds' => []];
        foreach ($mapping as $type => $fields) {
            $oid = $fields['oid'];
            unset($fields['oid']);
            foreach ($fields as $field) {
                $this->initializeDsQueryObject(); // We need to instantiate a new object here to remove where conditions
                $this->setDSParameters($type, [$field], $uid, $oid);
                if (!isset($result['ds'][$type])) {
                    $result['ds'][$type] = [];
                }
                if (!isset($result['ds'][$type]['data'])) {
                    $result['ds'][$type]['data'] = [];
                }
                try {
                    $result['ds'][$type]['data'][$field] = null;
                    $dsGet = $this->_dsQueryObject->dsGet();
                    $result['ds'][$type]['data'][$field] = $this->parseData($dsGet, FetchMethod::OPTION_FETCH_METHOD_GET, $field);
                } catch (GSApiException $e) {
                    $this->_logger->error($e->getMessage(), $e->getTrace());
                } catch (\Exception $e) {
                    $this->_logger->error($e->getMessage(), $e->getTrace());
                }
            }
        }
        return $result;
    }

    /**
     * Method used to call search method from DS api
     *
     * @param string $uid
     * @return array
     */
    public function search($uid)
    {
        $mapping = $this->getMapping();
        $result = ['ds' => []];
        foreach ($mapping as $type => $fields) {
            $this->initializeDsQueryObject(); // We need to instantiate a new object here to remove where conditions
            $this->_dsQueryObject->setQuery(null);
            $this->setDSParameters($type, $fields, $uid);
            $this->_dsQueryObject->addWhere('UID', '=', $uid);
            $this->_dsQueryObject->addWhere('oid', '=', $this->_dsQueryObject->getOid());
            try {
                $result['ds'][$type] = $this->parseData($this->_dsQueryObject->dsSearch(), FetchMethod::OPTION_FETCH_METHOD_SEARCH);
                if (empty($result['ds'][$type])) {
                    $result['ds'][$type] = $this->initializeEmptyResultArray($type, $fields)[$type];
                }
            } catch (GSApiException $e) {
                $this->_logger->error($e->getMessage(), $e->getTrace());
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage(), $e->getTrace());
            }
        }

        return $result;
    }

    /**
     * Method used to make request to DS api
     * This will automaticaly retrieve method to use from backend config
     *
     * @param string $uid
     * @return array
     */
    public function fetchFromMapping($uid)
    {
        $data = $this->gigyaSyncHelper->getDSDataFromGigya();
        $dsData = [];

        //Create a new mapping array with only the ds mapping
        foreach ($data as $key => $value) {
            if ($this->isDsKey($key)) {
                $dsData[$key] = $value;
            }
        }
        $this->setDSData($dsData);

        //Return the data with method set in backend admin
        return $this->{$this->gigyaDSSyncConfigHelper->getDSRetrieveMethod()}($uid);
    }

    /**
     * Set DS parameters to dsQueryObject
     *
     * @param $type
     * @param $fields
     * @param $uid
     * @param null $oid
     */
    public function setDSParameters($type, $fields, $uid, $oid = null)
    {
        $this->_dsQueryObject->setTable($type);
        if (!isset($oid)) {
            $oid = $fields['oid'];
            unset($fields['oid']);
        }
        $this->_dsQueryObject->setOid($oid);
        $this->_dsQueryObject->setFields($fields);
        $this->_dsQueryObject->setUid($uid);
    }

    /**
     * Parse data retrieved by get or search method
     * Result format :
     *  [
     *      'ds' => [
     *          'type' => [
     *              'data' => 'value'
     *           ]
     *      ]
     *  ]
     *
     * @param $dataToParse
     * @param $method
     * @param null $field
     * @return array|null
     */
    public function parseData($dataToParse, $method, $field = null)
    {
        $parsedData = [];
        if ($dataToParse instanceof GSObject) {
            $dataSerialized = $dataToParse->serialize();
            if ($method == FetchMethod::OPTION_FETCH_METHOD_GET) {
                $parsedData = $this->parseGetData($dataSerialized, $field);
            } elseif ($method == FetchMethod::OPTION_FETCH_METHOD_SEARCH) {
                $parsedData = $this->parseSearchData($dataSerialized);
            }
        }

        return $parsedData;
    }

    /**
     * Parse data for get method
     * @param $getData
     * @param $field
     * @return null|array
     */
    public function parseGetData($getData, $field)
    {
        if (empty($getData['data']) || empty($getData['data'][$field])) {
            return [$field => null];
        }
        return $getData['data'][$field];
    }

    /**
     * Parse data for search method
     *
     * @param $searchData
     * @return array
     */
    public function parseSearchData($searchData)
    {
        $parsedData = [];
        if ($searchData['totalCount'] === 1
            && !empty($searchData['results'])
            && !empty($searchData['results'][0])){
            $parsedData = $searchData['results'][0];
        } elseif ($searchData['totalCount'] === 1 && !empty($searchData['results'])) {
            foreach ($searchData['results'] as $result) {
                array_push($parsedData, $result);
            }
        }

        return $parsedData;
    }

    /**
     * Function to check if the key start with "ds."
     *
     * @param $key
     * @return bool
     */
    public function isDsKey($key)
    {
        if (strpos($key, "ds.") === 0) {
            return true;
        }

        return false;
    }

    /**
     * Initialize empty result array
     *
     * @param $type
     * @param $fields
     * @return array
     */
    public function initializeEmptyResultArray($type, $fields)
    {
        $resultArray = [$type => []];

        foreach ($fields as $key => $field) {
            if (is_int($key)) {
                $resultArray[$type]['data'][$field] = null;
            }
        }

        return $resultArray;
    }

}