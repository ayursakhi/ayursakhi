<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

class LookbookData extends \Magento\Framework\Model\AbstractModel
{
    protected $storeManager;
        
    protected $lookbookFactory;
    
    protected $categoryFactory;
    
    protected $itemFactory;
    
    protected $helper;
    
    protected $storeId;
    
    protected $fixtureManager;

	protected $csvReader;
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Codazon\Lookbookpro\Model\LookbookCategoryFactory $categoryFactory,
        \Codazon\Lookbookpro\Model\LookbookFactory $lookbookFactory,
        \Codazon\Lookbookpro\Model\LookbookItemFactory $itemFactory,
        \Codazon\Lookbookpro\Helper\Data $helper,
        SampleDataContext $sampleDataContext
    ) {
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->lookbookFactory = $lookbookFactory;
        $this->itemFactory = $itemFactory;
        $this->helper = $helper;
        $this->storeId = $this->storeManager->getStore()->getId();
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
    }
    
    public function exportLookbooksToCSV()
    {
        $collection = $this->lookbookFactory->create()
            ->getCollection()->setStoreId(0)
            ->addAttributeToSelect('*');
        $data = [];
        $data[0] = [];
		$result = [];
		$i = 0;
        $attributes = ['cover','description','is_active','meta_description','meta_keywords','meta_title','name','thumbnail','url_key'];
        foreach ($collection->getItems() as $item) {
			$itemData = $item->getData();
            if ($i === 0) {
                $data[0] = array_unique(array_merge($attributes, array_keys($itemData)));
            }
            $i++;
            foreach($data[0] as $attr) {
                $data[$i][$attr] = $item->getData($attr);
            }
			$message[] = '<li>'.$item->getData('name').'</li>';
            
		}
        $file = $this->fixtureManager->getFixture('Codazon_Lookbookpro::fixtures/lookbooks.csv');
        $this->csvReader->saveData($file, $data);
        $result['message'] = '<ul>' . implode('', $message) . '</ul>';
        return $result;
    }
    
    public function exportLookbookItemsToCSV()
    {
        $collection = $this->itemFactory->create()
            ->getCollection()->setStoreId(0)
            ->addAttributeToSelect('*')
            ->setPageSize(10000);
        $data = [];
		$result = [];
		$i = 0;
        $attributes = ['description','is_active','item_data','name'];
        foreach ($collection->getItems() as $item) {
			$itemData = $item->getData();
            if ($i === 0) {
                $data[0] = array_unique(array_merge($attributes, array_keys($itemData)));
            }
            $i++;
            foreach($data[0] as $attr) {
                $data[$i][$attr] = $item->getData($attr);
            }
			$message[] = '<li>'.$item->getData('name').'</li>';
            
		}
        $file = $this->fixtureManager->getFixture('Codazon_Lookbookpro::fixtures/lookbook_items.csv');
        $this->csvReader->saveData($file, $data);
        $result['message'] = '<ul>' . implode('', $message) . '</ul>';
        
        $data = [];
        $i = 0;
        $connection = $collection->getConnection();
        $select = $connection->select()->from(['cig' => $connection->getTableName('cdzlookbook_item_group')])->limit(10000);
        $itemgroup = $connection->fetchAll($select);
        foreach ($itemgroup as $group) {
            if ($i === 0) {
                $data[0] = array_keys($group);
            }
            $i++;
            $data[$i] = $group;
        }
        $file = $this->fixtureManager->getFixture('Codazon_Lookbookpro::fixtures/lookbook_item_groups.csv');
        $this->csvReader->saveData($file, $data);
        
        return $result;
    }
    
    public function exportLookbookCatagoriesToCSV()
    {
        $collection = $this->categoryFactory->create()
            ->getCollection()->setStoreId(0)
            ->addAttributeToSelect('*')
            ->setPageSize(10000);
        $collection->load();
        $data = [];
        $data[0] = [];
		$result = [];
		$i = 0;
        $attributes = ['cover','description','is_active','is_anchor','meta_description','meta_keywords','meta_title','name','thumbnail','url_key','url_path'];
        foreach ($collection->getItems() as $item) {
            $itemData = $item->getData();
           
            if ($i === 0) {
                $data[0] = array_unique(array_merge($attributes, array_keys($itemData)));
            }
            $i++;
            foreach($data[0] as $attr) {
                $data[$i][$attr] = $item->getData($attr);
            }
			$message[] = '<li>'.$item->getData('name').'</li>';
            
		}
        $file = $this->fixtureManager->getFixture('Codazon_Lookbookpro::fixtures/lookbook_categories.csv');
        $this->csvReader->saveData($file, $data);
        $result['message'] = '<ul>' . implode('', $message) . '</ul>';
        
        
        $data = [];
        $i = 0;
        $connection = $collection->getConnection();
        $select = $connection->select()->from(['cig' => $connection->getTableName('cdzlookbook_category_lookbook')])->limit(10000);
        $itemgroup = $connection->fetchAll($select);
        foreach ($itemgroup as $group) {
            if ($i === 0) {
                $data[0] = array_keys($group);
            }
            $i++;
            $data[$i] = $group;
        }
        $file = $this->fixtureManager->getFixture('Codazon_Lookbookpro::fixtures/category_lookbook.csv');
        $this->csvReader->saveData($file, $data);
        
        return $result;
    }
    
    public function importLookbooks()
    {
        $file = $this->fixtureManager->getFixture('Codazon_Lookbookpro::fixtures/lookbooks.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            
            $item = $this->lookbookFactory->create()->setStoreId(0);
            $item->addData($data);
            //$item->setIsActive(1);
            $item->save();
            $item->unsetData();
            
        }
    }
    
    public function importLookbookItems()
    {
        $file = $this->fixtureManager->getFixture('Codazon_Lookbookpro::fixtures/lookbook_items.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $item = $this->itemFactory->create()->setStoreId(0);
            $item->addData($data);
            $item->setIsActive(1);
            $item->save();
            $item->unsetData();
        }
    }
    
    public function importLookbookCategories()
    {
        $file = $this->fixtureManager->getFixture('Codazon_Lookbookpro::fixtures/lookbook_categories.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);

        $header = array_shift($rows);
        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                if (isset($header[$key])) {
                    $data[$header[$key]] = $value;
                }
            }
            $item = $this->categoryFactory->create()->setStoreId(0);
            $item->addData($data);
            $item->setIsActive(1);
            $item->save();
            $item->unsetData();
        }
    }
    
    public function assignLookbookToCategory()
    {
        $file = $this->fixtureManager->getFixture('Codazon_Lookbookpro::fixtures/category_lookbook.csv');
        if (!file_exists($file)) {
            return false;
        }
        $collection = $this->categoryFactory->create()->getCollection();
        $connection = $collection->getConnection();
        $sqlData = [];
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $sqlData[] = $data;
        }
        if (count($sqlData)) {
            $connection->insertMultiple($connection->getTableName('cdzlookbook_category_lookbook'), $sqlData);
        }
    }
    
    public function assignItemToLookbook()
    {
        $file = $this->fixtureManager->getFixture('Codazon_Lookbookpro::fixtures/lookbook_item_groups.csv');
        if (!file_exists($file)) {
            return false;
        }
        $collection = $this->itemFactory->create()->getCollection();
        $connection = $collection->getConnection();
        $sqlData = [];
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $sqlData[] = $data;
        }
        if (count($sqlData)) {
            $connection->insertMultiple($connection->getTableName('cdzlookbook_item_group'), $sqlData);
        }
    }
}
