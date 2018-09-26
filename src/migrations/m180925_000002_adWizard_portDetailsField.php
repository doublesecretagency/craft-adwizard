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

use yii\base\Exception;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\fields\PlainText;
use craft\models\FieldGroup;

/**
 * Migration: Port the old "Details" field
 * @since 2.1.0
 */
class m180925_000002_adWizard_portDetailsField extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // If field handle is taken, bail
        if ($this->db->columnExists('{{%content}}', 'field_adWizard_details')) {
            return;
        }
        $this->_createNewField();
        $this->_copyFieldValues();
        $this->_deleteOldField();
    }

    // Create a new custom field to replace "Details"
    private function _createNewField()
    {
        $fieldsService = Craft::$app->getFields();

        // Create field group
        $fieldGroup = new FieldGroup([
            'name' => "Ad Wizard",
        ]);
        Craft::$app->getFields()->saveGroup($fieldGroup);

        // Create field
        $field = $fieldsService->createField([
            'groupId' => $fieldGroup->id,
            'type' => PlainText::class,
            'name' => "Details",
            'handle' => 'adWizard_details',
            'instructions' => "",
            'settings' => [
                'multiline' => true,
                'initialRows' => 6
            ],
        ]);

        // Save field
        if (!$fieldsService->saveField($field)) {
            throw new Exception('Ad Wizard migration error: Unable to create "Details" field.');
        }

    }

    // Copy existing field values
    private function _copyFieldValues()
    {
        // Get existing data
        $ads = (new Query())
            ->select(['id', 'details'])
            ->from(['{{%adwizard_ads}}'])
            ->all($this->db);

        // Port data to new column
        foreach ($ads as $row) {
            $this->update(
                '{{%content}}',
                ['field_adWizard_details' => $row['details']],
                ['elementId' => $row['id']]
            );
        }
    }

    // Delete old fixed "Details" field
    private function _deleteOldField()
    {
        $this->dropColumn('{{%adwizard_ads}}', 'details');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180925_000002_adWizard_portDetailsField cannot be reverted.\n";

        return false;
    }

}
