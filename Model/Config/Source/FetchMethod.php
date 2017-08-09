<?php

namespace Gigya\GigyaDS\Model\Config\Source;

/**
 * Fetch method allowed in config
 *
 */
class FetchMethod implements \Magento\Framework\Option\ArrayInterface
{
    /** const for ds search method */
    const OPTION_FETCH_METHOD_SEARCH = 'search';

    /** const from ds get method */
    const OPTION_FETCH_METHOD_GET = 'get';

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::OPTION_FETCH_METHOD_SEARCH, 'label' => __('Search')],
            ['value' => self::OPTION_FETCH_METHOD_GET, 'label' => __('Get')]
        ];
    }
}
