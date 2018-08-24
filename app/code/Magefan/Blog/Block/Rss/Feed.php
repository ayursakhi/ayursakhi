<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Rss;

use Magento\Store\Model\ScopeInterface;

/**
 * Blog ree feed block
 */
class Feed extends \Magefan\Blog\Block\Post\PostList\AbstractList
{
    /*
     * Collection page size
     */
    const PAGE_SIZE = 10;

    /**
     * Retrieve rss feed url
     * @return string
     */
    public function getLink()
    {
        return $this->_url->getUrl('feed', 'rss');
    }

    /**
     * Retrieve rss feed title
     * @return string
     */
    public function getTitle()
    {
         return $this->_scopeConfig->getValue('mfblog/rss_feed/title', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve rss feed description
     * @return string
     */
    public function getDescription()
    {
         return $this->_scopeConfig->getValue('mfblog/rss_feed/description', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve rss feed collection size
     * @return string
     */
    public function getPageSize()
    {
        return $this->getData('page_size') ?: self::PAGE_SIZE;
    }
}
