<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Setup;

use Magento\Eav\Setup\EavSetup;

class LookbookproSetup extends EavSetup
{
    
    public function getDefaultEntities()
    {
        $entities = array (
            'cdzlookbook' => array (
                'entity_type_id' => 20,
                'entity_model' => 'Codazon\Lookbookpro\Model\ResourceModel\Lookbook',
                'attribute_model' => 'Codazon\Lookbookpro\Model\LookbookAttribute',
                'entity_attribute_collection' => 'Codazon\Lookbookpro\Model\ResourceModel\LookbookAttribute\Collection',
                'table' => 'cdzlookbook_entity',
                'attributes' => array (
                    'name' => array (
                        'type' => 'varchar',
                        'label' => 'Lookbook name',
                        'input' => 'text',
                        'required' => true,
                        'sort_order' => 5,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                    ),
                    'is_active' => array (
                        'type'  => 'int',
                        'label' => 'Is Active',
                        'input' => 'select',
                        'required' => true,
                        'sort_order' => 6,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                        'source_model'  =>  'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
                    ),
                    'description' => array (
                        'type' => 'text',
                        'label' => 'Lookbook description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 10,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                    ),
                    'thumbnail' => array (
                        'type' => 'varchar',
                        'label' => 'Lookbook thumbnail',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 15,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                    ),
                    'cover' => array (
                        'type' => 'varchar',
                        'label' => 'Lookbook cover',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 20,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                    ),
                    'url_key' => array (
                        'type' => 'varchar',
                        'label' => 'Url key',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 25,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                    ),
                    'meta_title' => array (
                        'type' => 'varchar',
                        'label' => 'Meta title',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 30,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                    ),
                    'meta_description' => array (
                        'type' => 'text',
                        'label' => 'Meta description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 35,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                    ),
                    'meta_keywords' => array (
                        'type' => 'varchar',
                        'label' => 'Meta keywords',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 40,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                    ),
                ),
            ),
            'cdzlookbook_item' => array (
                'entity_type_id' => 21,
                'entity_model' => 'Codazon\Lookbookpro\Model\ResourceModel\LookbookItem',
                'attribute_model' => 'Codazon\Lookbookpro\Model\LookbookItemAttribute',
                'entity_attribute_collection' => 'Codazon\Lookbookpro\Model\ResourceModel\LookbookItemAttribute\Collection',
                'table' => 'cdzlookbook_item_entity',
                'attributes' => array (
                    'name' => array (
                        'type' => 'varchar',
                        'label' => 'Item name',
                        'input' => 'text',
                        'required' => true,
                        'sort_order' => 5,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Item',
                    ),
                    'is_active' => array (
                        'type'  => 'int',
                        'label' => 'Is Active',
                        'input' => 'select',
                        'required' => true,
                        'sort_order' => 6,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                        'source_model'  =>  'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
                    ),
                    'description' => array (
                        'type' => 'text',
                        'label' => 'Item description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 10,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Item',
                    ),
                    'item_data' => array (
                        'type' => 'text',
                        'label' => 'Item data',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 15,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Item',
                    ),
                ),
            ),
            'cdzlookbook_category' => array (
                'entity_type_id' => 22,
                'entity_model' => 'Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory',
                'attribute_model' => 'Codazon\Lookbookpro\Model\LookbookCategoryAttribute',
                'entity_attribute_collection' => 'Codazon\Lookbookpro\Model\ResourceModel\LookbookCategoryAttribute\Collection',
                'table' => 'cdzlookbook_category_entity',
                'attributes' => array (
                    'name' => array (
                        'type' => 'varchar',
                        'label' => 'Category name',
                        'input' => 'text',
                        'required' => true,
                        'sort_order' => 5,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Category',
                    ),
                    'is_active' => array (
                        'type'  => 'int',
                        'label' => 'Is Active',
                        'input' => 'select',
                        'required' => true,
                        'sort_order' => 6,
                        'default'    => 1,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook',
                        'source_model'  =>  'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
                    ),
                    'description' => array (
                        'type' => 'text',
                        'label' => 'Category description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 10,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Category',
                    ),
                    'thumbnail' => array (
                        'type' => 'varchar',
                        'label' => 'Category thumbnail',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 15,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Category',
                    ),
                    'cover' => array (
                        'type' => 'varchar',
                        'label' => 'Category cover',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 20,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Category',
                    ),
                    'url_key' => array (
                        'type' => 'varchar',
                        'label' => 'Url key',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 25,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Category',
                    ),
                    'meta_title' => array (
                        'type' => 'varchar',
                        'label' => 'Meta title',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 30,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Category',
                    ),
                    'meta_description' => array (
                        'type' => 'text',
                        'label' => 'Meta description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 35,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Category',
                    ),
                    'meta_keywords' => array (
                        'type' => 'varchar',
                        'label' => 'Meta keywords',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 40,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Category',
                    ),
                    'is_anchor' => array (
                        'type' => 'int',
                        'label' => 'Is Anchor',
                        'input' => 'select',
                        'required' => false,
                        'sort_order' => 50,
                        'default'    => 1,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Category',
                    ),
                    'url_path' => array (
                        'type' => 'varchar',
                        'label' => 'Request Path',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 60,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Lookbook Category',
                    ),
                ),
            ),
        );
        return $entities;
    }
}
