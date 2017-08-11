<?php

namespace Gigya\GigyaDS\Model;

use Gigya\CmsStarterKit\fieldMapping;
use Gigya\GigyaDS\Helper\GigyaDSSyncConfigHelper;
use Gigya\GigyaDS\Helper\GigyaDSSyncHelper;
use Gigya\GigyaIM\Model\Cache\Type\FieldMapping as CacheTypeIM;
use Gigya\GigyaDS\Model\Cache\Type\FieldMapping as CacheTypeDS;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Gigya\GigyaIM\Logger\Logger as GigyaLogger;

/**
 * Class DSMagentoCustomerFieldsUpdater
 *
 * @inheritdoc
 * Override parent method to include the DS data into the mapping routine
 *
 * @package Gigya\GigyaDS\Model
 */
class DSMagentoCustomerFieldsUpdater extends \Gigya\GigyaIM\Model\MagentoCustomerFieldsUpdater
{
    /** @var GigyaDSSyncConfigHelper $dsSyncConfigHelper */
    public $dsSyncConfigHelper;

    /** @var  string $_dsFilePath */
    protected $_dsFilePath;

    /** @var GigyaDSSyncHelper $dsSyncHelper */
    public $dsSyncHelper;

    /** @var State $state */
    protected $state;

    /**
     * DSMagentoCustomerFieldsUpdater constructor.
     * @param CacheTypeIM $gigyaCacheType
     * @param EventManagerInterface $eventManager
     * @param GigyaLogger $logger
     * @param GigyaDSSyncConfigHelper $dsSyncConfigHelper
     */
    public function __construct(
        CacheTypeIM $gigyaCacheType,
        EventManagerInterface $eventManager,
        GigyaLogger $logger,
        GigyaDSSyncConfigHelper $dsSyncConfigHelper,
        State $state
    )
    {
        parent::__construct(
            $gigyaCacheType,
            $eventManager,
            $logger
        );

        $this->dsSyncConfigHelper = $dsSyncConfigHelper;
        $this->confMapping = [];
        $this->state = $state;
    }

    /**
     * Get the field mapping from cache or if cache is empty from the file mapping
     * Override the parent method to add DS field mapping
     *
     * @throws \Exception
     */
    public function retrieveFieldMappings()
    {
        //The DS need to works even if the CMS sync mapping fail
        try {
            parent::retrieveFieldMappings();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        //Prevent loading of mapping for backend
        if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
            return;
        }

        try {
            $conf = $this->getMappingFromCache(CacheTypeDS::CACHE_TAG, CacheTypeDS::TYPE_IDENTIFIER);
            if (false === $conf) {

                $mappingDSJson = file_get_contents($this->getDSPath());
                if (false === $mappingDSJson) {
                    $err     = error_get_last();
                    $message = "Could not retrieve field ds mapping configuration file. message was:" . $err['message'];
                    throw new \Exception("$message");
                }

                $conf = new fieldMapping\Conf($mappingDSJson);
                $this->setMappingCache($conf, CacheTypeDS::CACHE_TAG, CacheTypeDS::TYPE_IDENTIFIER);
            }

            //If the parent::retrieveFieldMapping does not work the gigyaMapping is not set so we only retrieve the DS mapping
            if (is_array($this->getGigyaMapping())) {
                $fullMapping = array_merge($this->getGigyaMapping(), $conf->getGigyaKeyed());
            } else {
                $fullMapping = $conf->getGigyaKeyed();
            }
            $this->setGigyaMapping($fullMapping);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * Set the DS file path
     *
     * @param $filePath
     */
    public function setDSPath($filePath)
    {
        $this->_dsFilePath = $filePath;
    }

    /**
     * Get the DS file path
     *
     * @return mixed
     */
    public function getDSPath()
    {
        return $this->_dsFilePath;
    }

    /**
     * @inheritdoc
     *
     * If the cache is deactivated we put the data on the attribute self::confMapping
     *
     * @param fieldMapping\Conf $mappingConf
     */
    protected function setMappingCache($mappingConf, $cacheTag = CacheTypeIM::CACHE_TAG, $cacheTypeIdentifier = CacheTypeIM::TYPE_IDENTIFIER)
    {
        if (!$this->gigyaCacheType->test($cacheTag)) {
            $this->confMapping[$cacheTag] = $mappingConf;
        } else {
            $this->gigyaCacheType->save(serialize($mappingConf), $cacheTypeIdentifier, [$cacheTag],
                86400);
        }
    }

    /**
     * @inheritdoc
     *
     * @return fieldMapping\Conf|false False if the cache is deactivated and the method self::setMappingCache() has not been called yet on this instance.
     */
    protected function getMappingFromCache($cacheTag = CacheTypeIM::CACHE_TAG, $cacheTypeIdentifier = CacheTypeIM::TYPE_IDENTIFIER)
    {
        if (!$this->gigyaCacheType->test($cacheTag)) {
            if (!array_key_exists($cacheTag, $this->confMapping)) {
                return false;
            }
            return $this->confMapping[$cacheTag];
        }

        return unserialize($this->gigyaCacheType->load($cacheTypeIdentifier));
    }

    /**
     * @inheritdoc
     */
    public function callCmsHook() {
        parent::callCmsHook();
        $gigya_user = array("gigya_user" => $this->getGigyaUser());
        $this->eventManager->dispatch("gigya_client_gigya_ds_data_alter", $gigya_user);
    }
}