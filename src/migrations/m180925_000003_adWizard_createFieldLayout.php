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

use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\records\FieldLayout as FieldLayoutRecord;

/**
 * Migration: Create a new field layout
 * @since 2.1.0
 */
class m180925_000003_adWizard_createFieldLayout extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $layoutId = $this->_createFieldLayout();
        $this->_attachLayoutToGroups($layoutId);
        $this->_attachLayoutToAds($layoutId);
    }

    // Create a new field layout
    private function _createFieldLayout()
    {
        // Get new "Details" field
        $detailsField = Craft::$app->getFields()->getFieldByHandle('adWizard_details');

        // If field not found, bail
        if (!$detailsField) {
            return;
        }

        // Configure field layout
        $layout = [
            'Content' => [$detailsField->id]
        ];

        // Assemble the Craft field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayout($layout);
        $fieldLayout->type = Ad::class;

        // Save Craft field layout
        if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
            throw new Exception('Ad Wizard migration error: Couldn’t save field layout.');
        }

        // Create Ad Wizard layout record
        $layoutRecord = new FieldLayoutRecord();
        $layoutRecord->id = $fieldLayout->id;
        $layoutRecord->name = "Custom Ad Fields";

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Save it!
            $layoutRecord->save(false);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        // Return new layout ID
        return $layoutRecord->id;
    }

    // Attach new layout to all groups
    private function _attachLayoutToGroups($layoutId)
    {
        try {
            $this->update(
                '{{%adwizard_groups}}',
                ['fieldLayoutId' => $layoutId]
            );
        } catch (Exception $e) {
        }
    }

    // Attach new layout to all ads
    private function _attachLayoutToAds($layoutId)
    {
        try {
            $this->update(
                '{{%elements}}',
                ['fieldLayoutId' => $layoutId],
                ['type' => Ad::class]
            );
        } catch (Exception $e) {
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180925_000003_adWizard_createFieldLayout cannot be reverted.\n";

        return false;
    }

}
