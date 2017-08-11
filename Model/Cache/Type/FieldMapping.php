<?php
namespace Gigya\GigyaDS\Model\Cache\Type;

/**
 * Class FieldMapping
 *
 * System / Cache Management / Cache type "Custom Cache Tag"
 *
 * @package Gigya\GigyaDS\Model\Cache\Type
 */
class FieldMapping extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'gigyaim_fieldmapping_ds_cache';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'GIGYAIM_FIELDMAPPING_DS_TAG';

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}