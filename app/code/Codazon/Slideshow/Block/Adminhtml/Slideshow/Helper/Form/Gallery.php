<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile



namespace Codazon\Slideshow\Block\Adminhtml\Slideshow\Helper\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Eav\Model\Entity\Attribute;

class Gallery extends AbstractElement
{

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getContentHtml();
        return $html;
    }

    /**
     * Prepares content block
     *
     * @return string
     */
    public function getContentHtml()
    {

        /* @var $content \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content */

        $content = $this->_renderer->getLayout()->createBlock('Codazon\Slideshow\Block\Adminhtml\Slideshow\Helper\Form\Gallery\Content');
        $content->setValue($this->getData('values'));
        $content->setId($this->getHtmlId() . '_content')->setElement($this);
        $galleryJs = $content->getJsObjectName();
        $content->getUploader()->getConfig()->setMegiaGallery($galleryJs);
        return $content->toHtml();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return '';
    }

    
   

    /**
     * Retrieve data object related with form
     *
     *@return mixed
     */
    public function getDataObject()
    {
        return $this->getForm()->getDataObject();
    }

    

    

    /**
     * @return string
     */
    public function toHtml()
    {
        return '<tr><td class="value" colspan="3">' . $this->getElementHtml() . '</td></tr>';
    }

    
}
