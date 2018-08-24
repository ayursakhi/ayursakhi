<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Codazon\AjaxLayeredNav\Block;

class Block extends \Magento\Framework\View\Element\Js\Components
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterAbleAttributeList,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $coreRegistry;
        $this->_filterAbleAttributeList = $filterAbleAttributeList;
        $this->_categoryFactory = $categoryFactory;
    }
    
    public function sluggable($str) {
        $before = array(
            'àáâãäåòóôõöøèéêëðçìíîïùúûüñšž',
            '/[^a-z0-9\s]/',
            array('/\s/', '/--+/', '/---+/')
        );
     
        $after = array(
            'aaaaaaooooooeeeeeciiiiuuuunsz',
            '',
            '-'
        );

        $str = strtolower($str);
        $str = strtr($str, $before[0], $after[0]);
        $str = preg_replace($before[1], $after[1], $str);
        $str = trim($str);
        $str = preg_replace($before[2], $after[2], $str);
     
        return $str;
    }
    
    public function getTreeCategories($parentId, $isChild){
        $html = '';
        $allCats = $this->_categoryFactory->create()->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('parent_id',array('eq' => $parentId));
        foreach ($allCats as $category){
            $html .= $category->getId().",";
            $subcats = $category->getChildren();
            if($subcats != ''){
                $html .= $this->getTreeCategories($category->getId(), true);
            }
        }
        return $html;
    }
    
    public function getAttributes()
    {
        $result = array();
        //$categories = $this->getCurrentCategory()->getChildrenCategories();
        
        $ids = $this->getTreeCategories($this->getCurrentCategory()->getId(),false);
        $categories = $this->_categoryFactory->create()->getCollection();
        $categories->addAttributeToSelect('name');
        $categories->addAttributeToFilter('entity_id',explode(',',$ids));
        foreach($categories as $cat)
        {
            $id = $cat->getId();
            $name = $this->sluggable($cat->getName());
            $result['cat'][$id] = $name;
        }
        foreach($this->_filterAbleAttributeList->getList() as $att)
        {
            $name = $att->getAttributeCode();
            $result[$name] = array();
            $opts = $att->getSource()->getAllOptions(false);
            foreach($opts as $opt)
            {
                $v = $opt['value'];
                $l = $this->sluggable($opt['label']);
                $result[$name][$v] = $l;
            }
        }
        return $result;
    }
    
    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }
}
