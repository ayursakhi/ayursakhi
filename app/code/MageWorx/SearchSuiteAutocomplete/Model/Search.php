<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteAutocomplete\Model;

use \MageWorx\SearchSuiteAutocomplete\Helper\Data as HelperData;
use \MageWorx\SearchSuiteAutocomplete\Model\SearchFactory;

/**
 * Search class returns needed search data
 */
class Search
{
    /**
     * @var \MageWorx\SearchSuiteAutocomplete\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\SearchSuiteAutocomplete\Model\SearchFactory
     */
    protected $searchFactory;

    /**
     * Search constructor.
     * @param \MageWorx\SearchSuiteAutocomplete\Model\SearchFactory $searchFactory
     */
    public function __construct(
        HelperData $helperData,
        SearchFactory $searchFactory
    ) {
        $this->helperData = $helperData;
        $this->searchFactory = $searchFactory;
    }

    /**
     * Retrieve suggested, product data
     *
     * @return array
     */
    public function getData()
    {
        $data = [];
        $autocompleteFields = $this->helperData->getAutocompleteFieldsAsArray();

        foreach ($autocompleteFields as $field) {
           $data[] = $this->searchFactory->create($field)->getResponseData();
        }

        return $data;
    }
}
