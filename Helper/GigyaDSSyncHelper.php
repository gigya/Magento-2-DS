<?php

namespace Gigya\GigyaDS\Helper;

use Gigya\GigyaDS\Model\DSMagentoCustomerFieldsUpdater;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Gigya\GigyaIM\Logger\Logger as GigyaLogger;
use Magento\Framework\App\Helper\Context;


class GigyaDSSyncHelper extends AbstractHelper
{
    protected $_scopeConfig;
    protected $_logger;
    protected $_mappingPath;
    protected $_method;
    protected $_customerFieldsUpdater;
    public $_dsSyncConfigHelper;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GigyaLogger $logger,
        Context $context,
        DSMagentoCustomerFieldsUpdater $customerFieldsUpdater,
        GigyaDSSyncConfigHelper $dsyncConfigHelper
    )
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_customerFieldsUpdater = $customerFieldsUpdater;
        $this->_dsSyncConfigHelper = $dsyncConfigHelper;
    }

    public function getDSDataFromGigya()
    {
        $imMapping = $this->_scopeConfig->getValue("gigya_section_fieldmapping/general_fieldmapping/mapping_file_path");
        $this->_customerFieldsUpdater->setPath($imMapping);
        $this->_customerFieldsUpdater->setDSPath($this->_dsSyncConfigHelper->getDSMappingPath());
        $this->_customerFieldsUpdater->retrieveFieldMappings();
        $fieldMapping = $this->_customerFieldsUpdater->getGigyaMapping();

        return $fieldMapping;
    }
}