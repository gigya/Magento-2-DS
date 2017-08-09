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
     * Given a Magento customer, retrieves the corresponding Gigya account data from the Gigya service.
     *
     * @param $magentoCustomer
     * @return array [
     *                  'gigya_user' => GigyaUser : the data from the Gigya service
     *                  'gigya_logging_email' => string : the email for logging as set on this Gigya account
     *               ]
     */
    protected function getGigyaDataForEnrichment($magentoCustomer)
    {
        $gigyaAccountData = $this->gigyaAccountRepository->get($magentoCustomer->getGigyaUid());
        $gigyaAccountLoggingEmail = $this->gigyaSyncHelper->getMagentoCustomerAndLoggingEmail($gigyaAccountData)['logging_email'];
        $gigyaDSData = $this->gigyaDSService->fetchFromMapping($magentoCustomer->getGigyaUid());
        $gigyaAccountData->setDs($gigyaDSData['ds']);


        return [
            'gigya_user' => $gigyaAccountData,
            'gigya_logging_email' => $gigyaAccountLoggingEmail,
        ];
    }
}