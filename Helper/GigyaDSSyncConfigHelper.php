<?php

namespace Gigya\GigyaDS\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;


class GigyaDSSyncConfigHelper extends AbstractHelper
{
    protected $_scopeConfig;
    protected $_method;
    protected $_mappingPath;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context
    )
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
    }


    public function getDSMappingPath()
    {
        if (empty($this->_mappingPath)) {
            $this->_mappingPath = $this->scopeConfig->getValue('gigya_section_datastorage_settings/datastorage_mapping_file_path/mapping_file_path');;
        }
        return $this->_mappingPath;
    }

    public function getDSRetrieveMethod()
    {
        if (empty($this->_method)){
            $this->_method = $this->scopeConfig->getValue('gigya_section_datastorage_settings/datastorage_fetch_method/used_fetch_method');
        }

        return $this->_method;
    }
}