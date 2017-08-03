<?php

namespace Gigya\GigyaDS\Model\Config\Source;

/**
 * Fetch method allowed in config
 *
 */
class FetchMethod implements \Magento\Framework\Option\ArrayInterface
{
    const OPTION_FETCH_METHOD_SEARCH = 'fetch';
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
            ['value' => self::OPTION_FETCH_METHOD_GET, 'label' => __('Fetch')]
        ];
    }
}
