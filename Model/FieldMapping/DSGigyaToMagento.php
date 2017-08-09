<?php

namespace Gigya\GigyaDS\Model\FieldMapping;

use Gigya\GigyaDS\Helper\GigyaDSSyncConfigHelper;
use Gigya\GigyaIM\Exception\GigyaFieldMappingException;
use Gigya\GigyaIM\Model\FieldMapping\GigyaToMagento;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Gigya\GigyaIM\Model\MagentoCustomerFieldsUpdater;
use Gigya\GigyaIM\Logger\Logger as GigyaLogger;

/**
 * Class DSGigyaToMagento
 * @package Gigya\GigyaDS\Model\FieldMapping
 */
class DSGigyaToMagento extends GigyaToMagento
{
    /** @var GigyaDSSyncConfigHelper $dsSyncConfigHelper*/
    public $dsSyncConfigHelper;

    /**
     * DSGigyaToMagento constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param GigyaLogger $logger
     * @param MagentoCustomerFieldsUpdater $customerFieldsUpdater
     * @param GigyaDSSyncConfigHelper $dsSyncConfigHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GigyaLogger $logger,
        MagentoCustomerFieldsUpdater $customerFieldsUpdater,
        GigyaDSSyncConfigHelper $dsSyncConfigHelper
    )
    {
        parent::__construct($scopeConfig, $logger, $customerFieldsUpdater);
        $this->dsSyncConfigHelper = $dsSyncConfigHelper;
    }

    /**
     * Override parent method to allow run on DSFieldMapping
     * even if the gigya_section_fieldmapping/general_fieldmapping/mapping_file_path does not exist
     * @param Customer $customer
     * @param $gigyaUser
     * @throws GigyaFieldMappingException
     */
    public function run($customer, $gigyaUser)
    {
        $config_file_path = $this->scopeConfig->getValue("gigya_section_fieldmapping/general_fieldmapping/mapping_file_path");
        $config_file_path_DS = $this->dsSyncConfigHelper->getDSMappingPath();
        if (!is_null($config_file_path) || !is_null($config_file_path_DS)) {
            $this->customerFieldsUpdater->setPath($config_file_path);
            $this->customerFieldsUpdater->setGigyaUser($gigyaUser);
            try {
                $this->customerFieldsUpdater->updateCmsAccount($customer);
            } catch (\Exception $e) {
                $message = "error " . $e->getCode() . ". message: " . $e->getMessage() . ". File: " .$e->getFile();
                $this->logger->error(
                    $message,
                    [
                        'class' => __CLASS__,
                        'function' => __FUNCTION__
                    ]
                );
                throw new GigyaFieldMappingException($message);
            }
        } else {
            $message = "mapping fields file path is not defined. Define file path at: Stores:Config:Gigya:Field Mapping and/or StoresConfig/Gigya/Data storage settings";
            $this->logger->error(
                $message,
                [
                    'class' => __CLASS__,
                    'function' => __FUNCTION__
                ]
            );
            throw new GigyaFieldMappingException($message);
        }
    }

}