<?php
/**
 * Copyright © 2016 X2i.
 */

namespace Gigya\GigyaDS\Observer;

use Gigya\GigyaDS\Api\GigyaDSRepositoryInterface;
use Gigya\GigyaDS\Helper\GigyaDSSyncHelper;
use Gigya\GigyaIM\Observer\FrontendMagentoCustomerEnricher;
use Gigya\GigyaIM\Api\GigyaAccountRepositoryInterface;
use Gigya\GigyaIM\Helper\GigyaSyncHelper;
use Gigya\GigyaIM\Model\FieldMapping\GigyaToMagento;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Gigya\GigyaIM\Logger\Logger as GigyaLogger;

class DSMagentoCustomerEnricher extends FrontendMagentoCustomerEnricher
{
    protected $gigyaDSSyncHelper;

    /**
     * FrontendMagentoCustomerEnricher constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param GigyaAccountRepositoryInterface $gigyaAccountRepository
     * @param GigyaSyncHelper $gigyaSyncHelper
     * @param GigyaLogger $logger
     * @param Context $context
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        GigyaAccountRepositoryInterface $gigyaAccountRepository,
        GigyaSyncHelper $gigyaSyncHelper,
        GigyaLogger $logger,
        Context $context,
        GigyaToMagento $gigyaToMagentoMapper,
        GigyaDSSyncHelper $gigyaDSSyncHelper
    ) {
        parent::__construct(
            $customerRepository,
            $gigyaAccountRepository,
            $gigyaSyncHelper,
            $logger,
            $context,
            $gigyaToMagentoMapper
        );
        $this->gigyaDSSyncHelper = $gigyaDSSyncHelper;
    }
    /**
     * Given a Magento customer, retrieves the corresponding Gigya account data from the Gigya service.
     *
     * @param $magentoCustomer
     * @return array [
     *                  'gigya_user' => GigyaUser : the data from the Gigya service
     *                  'gigya_logging_email' => string : the email for logging as set on this Gigya account
     *                  'gigya_ds' => array : the data from Gigya Data Store
     *               ]
     */
    protected function getGigyaDataForEnrichment($magentoCustomer)
    {
        $gigyaAccountData = $this->gigyaAccountRepository->get($magentoCustomer->getGigyaUid());
        $gigyaAccountLoggingEmail = $this->gigyaSyncHelper->getMagentoCustomerAndLoggingEmail($gigyaAccountData)['logging_email'];
        $gigyaDSData = $this->gigyaDSSyncHelper->get($magentoCustomer->getGigyaUid());

        return [
            'gigya_user' => $gigyaAccountData,
            'gigya_logging_email' => $gigyaAccountLoggingEmail
        ];
    }
}