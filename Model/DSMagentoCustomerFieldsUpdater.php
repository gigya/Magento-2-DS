<?php

namespace Gigya\GigyaDS\Model;

use Gigya\CmsStarterKit\fieldMapping;
use Gigya\GigyaDS\Helper\GigyaDSSyncConfigHelper;
use Gigya\GigyaIM\Model\Cache\Type\FieldMapping as CacheTypeIM;
use Gigya\GigyaDS\Model\Cache\Type\FieldMapping as CacheTypeDS;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Gigya\GigyaIM\Logger\Logger as GigyaLogger;


class DSMagentoCustomerFieldsUpdater extends \Gigya\GigyaIM\Model\MagentoCustomerFieldsUpdater
{
    public $dsSyncConfigHelper;
    protected $_dsFilePath;

    public function __construct(
        CacheTypeIM $gigyaCacheType,
        EventManagerInterface $eventManager,
        GigyaLogger $logger,
        GigyaDSSyncConfigHelper $dsSyncConfigHelper
    )
    {
        parent::__construct(
            $gigyaCacheType,
            $eventManager,
            $logger
        );

        $this->dsSyncConfigHelper = $dsSyncConfigHelper;
        $this->confMapping = [];
    }

    public function retrieveFieldMappings()
    {
        parent::retrieveFieldMappings();
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
        $fullMapping = array_merge($this->getGigyaMapping(), $conf->getGigyaKeyed());
        $this->setGigyaMapping($fullMapping);
    }

    public function setDSPath($filePath)
    {
        $this->_dsFilePath = $filePath;
    }

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





}