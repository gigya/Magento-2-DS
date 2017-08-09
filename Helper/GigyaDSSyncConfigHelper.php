<?php

namespace Gigya\GigyaDS\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Toolkit for GigyaDS
 *
 * Class GigyaDSSyncConfigHelper
 * @package Gigya\GigyaDS\Helper
 */
class GigyaDSSyncConfigHelper extends AbstractHelper
{
    /** @var ScopeConfigInterface $_scopeConfig */
    protected $_scopeConfig;

    /** @var  string $_method */
    protected $_method;

    /** @var  string $_mappingPath */
    protected $_mappingPath;

    /**
     * GigyaDSSyncConfigHelper constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context
    )
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
    }


    /**
     * Get the Gigya DS file mapping path from backend config
     *
     * @return string
     */
    public function getDSMappingPath()
    {
        if (empty($this->_mappingPath)) {
            $this->_mappingPath = $this->scopeConfig->getValue('gigya_section_datastorage_settings/datastorage_mapping_file_path/mapping_file_path');;
        }

        return $this->_mappingPath;
    }

    /**
     * Get the Gigya method to use from backend config
     *
     * @return string
     */
    public function getDSRetrieveMethod()
    {
        if (empty($this->_method)){
            $this->_method = $this->scopeConfig->getValue('gigya_section_datastorage_settings/datastorage_fetch_method/used_fetch_method');
        }

        return $this->_method;
    }
}