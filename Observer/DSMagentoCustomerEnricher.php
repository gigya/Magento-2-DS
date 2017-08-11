<?php
/**
 * Copyright © 2017 Clever-age.
 */

namespace Gigya\GigyaDS\Observer;

use Gigya\GigyaDS\Api\GigyaDSServiceInterface;
use Gigya\GigyaDS\Model\GigyaDSService;
use Gigya\GigyaIM\Observer\FrontendMagentoCustomerEnricher;
use Gigya\GigyaIM\Api\GigyaAccountRepositoryInterface;
use Gigya\GigyaIM\Helper\GigyaSyncHelper;
use Gigya\GigyaIM\Model\FieldMapping\GigyaToMagento;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Gigya\GigyaIM\Logger\Logger as GigyaLogger;
use Magento\Framework\App\Area;

/**
 * Class DSMagentoCustomerEnricher
 *
 * @inheritdoc
 *
 * Override to fetch DS Data
 *
 * @package Gigya\GigyaDS\Observer
 */
class DSMagentoCustomerEnricher extends FrontendMagentoCustomerEnricher
{
    /** @var GigyaDSService $gigyaDSService */
    protected $gigyaDSService;

    /**
     * DSMagentoCustomerEnricher constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param GigyaAccountRepositoryInterface $gigyaAccountRepository
     * @param GigyaSyncHelper $gigyaSyncHelper
     * @param GigyaLogger $logger
     * @param Context $context
     * @param GigyaToMagento $gigyaToMagentoMapper
     * @param GigyaDSServiceInterface $gigyaDSService
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        GigyaAccountRepositoryInterface $gigyaAccountRepository,
        GigyaSyncHelper $gigyaSyncHelper,
        GigyaLogger $logger,
        Context $context,
        GigyaToMagento $gigyaToMagentoMapper,
        GigyaDSServiceInterface $gigyaDSService
    ) {
        parent::__construct(
            $customerRepository,
            $gigyaAccountRepository,
            $gigyaSyncHelper,
            $logger,
            $context,
            $gigyaToMagentoMapper
        );
        $this->gigyaDSService = $gigyaDSService;
    }
    /**
     * @inheritdoc
     * Also retrieves the corresponding Gigya DS data from the Gigya service and store it into gigya_user
     */
    protected function getGigyaDataForEnrichment($magentoCustomer)
    {
        $result = parent::getGigyaDataForEnrichment($magentoCustomer);

        $gigyaDSData = $this->gigyaDSService->fetchFromMapping($magentoCustomer->getGigyaUid());
        $result['gigya_user']->setDs($gigyaDSData['ds']);

        return $result;
    }

}