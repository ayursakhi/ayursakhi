<?php
/**
 * Copyright © 2017 Aitoc. All rights reserved.
 */

namespace Aitoc\ReviewBooster\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the ReviewBooster module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->addSalesRuleIdField($setup);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->addUnsubscribeCodeField($setup);
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->addImageField($setup);
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->addCommentField($setup);
            $this->addAdminTitleField($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add sales_rule_id field to Reminder table
     *
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    private function addSalesRuleIdField(SchemaSetupInterface $setup)
    {
        $reminderTable = $setup->getTable('aitoc_review_booster_reminder');
        $salesRuleField = 'sales_rule_id';
        $connection = $setup->getConnection();
        $connection->addColumn($reminderTable, $salesRuleField, 'int');
    }

    /**
     * Add unsubscribe_code field to Reminder table
     *
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    private function addUnsubscribeCodeField(SchemaSetupInterface $setup)
    {
        $reminderTable = $setup->getTable('aitoc_review_booster_reminder');
        $unsubscribeCodeField = 'unsubscribe_code';
        $connection = $setup->getConnection();
        $connection->addColumn($reminderTable, $unsubscribeCodeField, 'text');
    }

    /**
     * Add image field to Review Detael Extended table
     *
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    private function addImageField(SchemaSetupInterface $setup)
    {
        $reminderTable = $setup->getTable('aitoc_review_booster_review_detail_extended');
        $imageField = 'image';
        $connection = $setup->getConnection();
        $connection->addColumn($reminderTable, $imageField, 'text');
    }

    private function addCommentField(SchemaSetupInterface $setup)
    {
        $reminderTable = $setup->getTable('aitoc_review_booster_review_detail_extended');
        $commentField = 'comment';
        $connection = $setup->getConnection();
        $connection->addColumn($reminderTable, $commentField, 'text');
    }

    private function addAdminTitleField(SchemaSetupInterface $setup)
    {
        $reminderTable = $setup->getTable('aitoc_review_booster_review_detail_extended');
        $titleField = 'admin_title';
        $connection = $setup->getConnection();
        $connection->addColumn($reminderTable, $titleField, 'text');
    }
}
