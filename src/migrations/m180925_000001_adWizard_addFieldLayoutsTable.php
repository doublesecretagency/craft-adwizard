<?php
/**
 * Ad Wizard plugin for Craft CMS
 *
 * Easily manage custom advertisements on your website.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2014 Double Secret Agency
 */

namespace doublesecretagency\adwizard\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * Migration: Add field layouts table
 * @since 2.1.0
 */
class m180925_000001_adWizard_addFieldLayoutsTable extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        $this->_createTable();
        $this->_addColumn();
        $this->_addForeignKeys();
    }

    /**
     * Create table
     */
    private function _createTable(): void
    {
        // If table already exists, bail
        if ($this->db->tableExists('{{%adwizard_fieldlayouts}}')) {
            return;
        }

        // Create new table
        $this->createTable('{{%adwizard_fieldlayouts}}', [
            'id'          => $this->integer()->notNull(),
            'name'        => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid(),
            'PRIMARY KEY([[id]])',
        ]);
    }

    /**
     * Add column
     *
     * @throws NotSupportedException
     */
    private function _addColumn(): void
    {
        // If column already exists, bail
        if ($this->db->columnExists('{{%adwizard_groups}}', 'fieldLayoutId')) {
            return;
        }

        // Add new column
        $this->addColumn('{{%adwizard_groups}}', 'fieldLayoutId', $this->integer()->after('id'));
    }

    /**
     * Add foreign keys
     */
    protected function _addForeignKeys(): void
    {
        $this->addForeignKey(null, '{{%adwizard_fieldlayouts}}', ['id'],            '{{%fieldlayouts}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%adwizard_groups}}',       ['fieldLayoutId'], '{{%fieldlayouts}}', ['id'], 'SET NULL');
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180925_000001_adWizard_addFieldLayoutsTable cannot be reverted.\n";

        return false;
    }

}
