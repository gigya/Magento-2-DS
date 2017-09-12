<?php

namespace Gigya\GigyaDS\Helper;

use Gigya\GigyaDS\Model\DSMagentoCustomerFieldsUpdater;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Gigya\GigyaIM\Logger\Logger as GigyaLogger;
use Magento\Framework\App\Helper\Context;

/**
 * Helper for generic method that could be used with DSSync
 *
 * Class GigyaDSSyncHelper
 * @package Gigya\GigyaDS\Helper
 */
class GigyaDSSyncHelper extends AbstractHelper
{
    /** @var ScopeConfigInterface $_scopeConfig */
    protected $_scopeConfig;

    /** @var GigyaLogger $_logger */
    protected $_logger;

    /** @var  string $_mappingPath */
    protected $_mappingPath;

    /** @var  string $_method */
    protected $_method;

    /** @var DSMagentoCustomerFieldsUpdater $_customerFieldsUpdater */
    protected $_customerFieldsUpdater;

    /** @var GigyaDSSyncConfigHelper $_dsSyncConfigHelper */
    public $_dsSyncConfigHelper;

    /**
     * GigyaDSSyncHelper constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param GigyaLogger $logger
     * @param Context $context
     * @param DSMagentoCustomerFieldsUpdater $customerFieldsUpdater
     * @param GigyaDSSyncConfigHelper $dsyncConfigHelper
     */
    public function __construct(
        GigyaLogger $logger,
        Context $context,
        DSMagentoCustomerFieldsUpdater $customerFieldsUpdater,
        GigyaDSSyncConfigHelper $dsyncConfigHelper
    )
    {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_logger = $logger;
        $this->_customerFieldsUpdater = $customerFieldsUpdater;
        $this->_dsSyncConfigHelper = $dsyncConfigHelper;
    }

    /**
     * Get the data from Gigya Basic data + DS
     * @return mixed
     */
    public function getDSDataFromGigya()
    {
        $imMapping = $this->_scopeConfig->getValue("gigya_section_fieldmapping/general_fieldmapping/mapping_file_path");
        $this->_customerFieldsUpdater->setPath($imMapping);
        $this->_customerFieldsUpdater->setDSPath($this->_dsSyncConfigHelper->getDSMappingPath());
        $this->_customerFieldsUpdater->retrieveFieldMappings();
        $fieldMapping = $this->_customerFieldsUpdater->getGigyaMapping();
        if (!is_array($fieldMapping)) {
            $fieldMapping = [];
        }
        
        return $fieldMapping;
    }

    /**
     * Transform the mapping to get only the request fields
     * Example :
     *  'ds.pets.data.nickname'
     *  'ds.pets.data.color'
     *   become
     *   [
     *      pets => [
     *         0 => nickname
     *         1 => color
     *      ]
     *    ]
     *
     *
     * @param $mapping
     * @return array
     */
    public function updateMappingForRequest($mapping)
    {
        $updatedMapping = [];
        foreach ($mapping as $data) {
            $dsMapping = explode('.', $data[0]->getGigyaName());
            if (empty($updatedMapping[$dsMapping[1]])) {
                $updatedMapping[$dsMapping[1]] = [];
                $updatedMapping[$dsMapping[1]] = $data[0]->getCustom();
            }
            array_push($updatedMapping[$dsMapping[1]], $dsMapping[3]);

        }

        return $updatedMapping;
    }
}