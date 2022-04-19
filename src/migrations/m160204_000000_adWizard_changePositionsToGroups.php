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
use craft\db\Query;
use craft\helpers\MigrationHelper;
use yii\base\NotSupportedException;

/**
 * Migration: Change "Positions" to "Groups"
 * @since 2.0.0
 */
class m160204_000000_adWizard_changePositionsToGroups extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        $this->_renameTable();
        $this->_renameForeignKey();
        $this->_renameWidgets();
    }

    /**
     * Rename table
     */
    private function _renameTable(): void
    {
        MigrationHelper::dropIndexIfExists('{{%adwizard_positions}}', ['name'], true, $this);
        MigrationHelper::dropIndexIfExists('{{%adwizard_positions}}', ['handle'], true, $this);
        $this->renameTable('{{%adwizard_positions}}', '{{%adwizard_groups}}');
        $this->createIndex(null, '{{%adwizard_groups}}', ['name'], true);
        $this->createIndex(null, '{{%adwizard_groups}}', ['handle'], true);
    }

    /**
     * Rename foreign key
     *
     * @throws NotSupportedException
     */
    private function _renameForeignKey(): void
    {
        // If column already exists, bail
        if ($this->db->columnExists('{{%adwizard_ads}}', 'groupId')) {
            return;
        }

        // Add new column
        $this->addColumn('{{%adwizard_ads}}', 'groupId', $this->integer()->notNull()->after('positionId'));

        // Get existing data
        $ads = (new Query())
            ->select(['id', 'positionId'])
            ->from(['{{%adwizard_ads}}'])
            ->all($this->db);

        // Port data to new column
        foreach ($ads as $row) {
            $newData = ['groupId' => $row['positionId']];
            $this->update('{{%adwizard_ads}}', $newData, ['id' => $row['id']]);
        }

        // Drop existing foreign key
        MigrationHelper::dropForeignKey('{{%adwizard_ads}}', ['positionId'], $this);
        $this->dropColumn('{{%adwizard_ads}}', 'positionId');

        // Convert new column to foreign key
        $this->addForeignKey(null, '{{%adwizard_ads}}', 'groupId', '{{%adwizard_groups}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * Rename widget
     */
    private function _renameWidgets(): void
    {
        // Get existing data
        $widgets = (new Query())
            ->select(['id', 'type', 'settings'])
            ->from(['{{%widgets}}'])
            ->where(['or',
                ['type' => 'AdWizard_AdTimeline'],
                ['type' => 'AdWizard_PositionTotals']
            ])
            ->all($this->db);

        // Rename widget and keys
        foreach ($widgets as $row) {
            $newData = [];
            switch ($row['type']) {
                case 'AdWizard_PositionTotals':
                    $newData['type'] = 'AdWizard_GroupTotals';
                case 'AdWizard_AdTimeline':
                    $newData['settings'] = str_replace('positionId', 'groupId', $row['settings']);
                    break;
            }
            if (!empty($newData)) {
                $this->update('{{%widgets}}', $newData, ['id' => $row['id']]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m160204_000000_adWizard_changePositionsToGroups cannot be reverted.\n";

        return false;
    }

}
