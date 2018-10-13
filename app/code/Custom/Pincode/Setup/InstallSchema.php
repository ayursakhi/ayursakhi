<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Custom\Pincode\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface{

    public function install(SchemaSetupInterface $setup,ModuleContextInterface $context){
        $installer = $setup;
        $installer->startSetup();

        /**
         * create table zipcode
         *
         */
        $table = $installer->getConnection()->newTable($installer->getTable('zipcode'))
                ->addColumn('id',\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,11,['identity' => true,'unsigned' => true,'nullable' => false,'primary' => true],'Entity ID'
                )
                ->addColumn('zipcode',\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,11,['unsigned' => true,'nullable' => false],'Zipcodes');
        $installer->getConnection()
                ->createTable($table);
        $installer->endSetup();
    }

}
