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

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\fields\PlainText;
use craft\helpers\ElementHelper;
use craft\helpers\Json;
use craft\models\FieldGroup;
use Throwable;
use yii\base\Exception;
use yii\base\NotSupportedException;

/**
 * Migration: Port the old "Details" field
 * @since 2.1.0
 */
class m180925_000002_adWizard_portDetailsField extends Migration
{

    /**
     * @inheritDoc
     * @return bool|void
     * @throws Exception
     * @throws Throwable
     * @throws NotSupportedException
     */
    public function safeUp()
    {
        $field = Craft::$app->getFields()->getFieldByHandle('adWizard_details');

        // If field handle is taken, bail
        if ($field) {
            return;
        }

        $this->_createNewField();
        $this->_copyFieldValues();
        $this->_deleteOldField();
    }

    /**
     * Create a new custom field to replace "Details"
     *
     * @throws Exception
     * @throws Throwable
     */
    private function _createNewField()
    {
        $fieldsService = Craft::$app->getFields();

        // Create field group
        $fieldGroup = new FieldGroup([
            'name' => 'Ad Wizard',
        ]);
        $fieldsService->saveGroup($fieldGroup);

        // Create field
        $field = $fieldsService->createField([
            'groupId' => $fieldGroup->id,
            'type' => PlainText::class,
            'name' => 'Details',
            'handle' => 'adWizard_details',
            'instructions' => '',
            'settings' => [
                'multiline' => true,
                'initialRows' => 6
            ],
        ]);

        // Save field
        if (!$fieldsService->saveField($field)) {
            throw new Exception('Ad Wizard migration error: Unable to create "Details" field ' . Json::encode($field->getErrors()) . ' .');
        }
    }

    /**
     * Copy existing field values
     */
    private function _copyFieldValues()
    {
        // Get existing data
        $ads = (new Query())
            ->select(['id', 'details'])
            ->from(['{{%adwizard_ads}}'])
            ->all($this->db);

        $field = Craft::$app->getFields()->getFieldByHandle('adWizard_details');
        $column = ElementHelper::fieldColumnFromField($field);

        // Port data to new column
        foreach ($ads as $row) {
            $this->update(
                '{{%content}}',
                [$column => $row['details']],
                ['elementId' => $row['id']]
            );
        }
    }

    /**
     * Delete old fixed "Details" field
     */
    private function _deleteOldField()
    {
        $this->dropColumn('{{%adwizard_ads}}', 'details');
    }

    /**
     * @inheritDoc
     */
    public function safeDown(): bool
    {
        echo "m180925_000002_adWizard_portDetailsField cannot be reverted.\n";

        return false;
    }

}
