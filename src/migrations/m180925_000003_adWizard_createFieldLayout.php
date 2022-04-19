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
use craft\fields\PlainText;
use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\records\FieldLayout as FieldLayoutRecord;
use Throwable;
use yii\base\Exception;
use yii\db\Transaction;

/**
 * Migration: Create a new field layout
 * @since 2.1.0
 */
class m180925_000003_adWizard_createFieldLayout extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        $layoutId = $this->_createFieldLayout();

        // If no layout was created, bail
        if (!$layoutId) {
            return;
        }

        $this->_attachLayoutToGroups($layoutId);
        $this->_attachLayoutToAds($layoutId);
    }

    /**
     * Create a new field layout
     *
     * @return int|null
     * @throws Exception
     * @throws Throwable
     */
    private function _createFieldLayout(): ?int
    {
        // Get new "Details" field
        /** @var PlainText $detailsField */
        $detailsField = Craft::$app->getFields()->getFieldByHandle('adWizard_details');

        // If field not found, bail
        if (!$detailsField) {
            return null;
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
        $layoutRecord->name = 'Custom Ad Fields';

        /** @var Transaction $transaction */
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Save it!
            $layoutRecord->save(false);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        // Return new layout ID
        return $layoutRecord->id;
    }

    /**
     * Attach new layout to all groups
     *
     * @param int $layoutId
     */
    private function _attachLayoutToGroups(int $layoutId): void
    {
        $this->update(
            '{{%adwizard_groups}}',
            ['fieldLayoutId' => $layoutId]
        );
    }

    /**
     * Attach new layout to all ads
     *
     * @param int $layoutId
     */
    private function _attachLayoutToAds(int $layoutId): void
    {
        $this->update(
            '{{%elements}}',
            ['fieldLayoutId' => $layoutId],
            ['type' => Ad::class]
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180925_000003_adWizard_createFieldLayout cannot be reverted.\n";

        return false;
    }

}
