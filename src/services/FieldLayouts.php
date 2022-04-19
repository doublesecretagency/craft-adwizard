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

namespace doublesecretagency\adwizard\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\db\Table;
use craft\models\FieldLayout as CraftFieldLayout;
use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\models\FieldLayout;
use doublesecretagency\adwizard\records\FieldLayout as FieldLayoutRecord;
use Exception;
use Throwable;
use yii\db\Transaction;

/**
 * Class FieldLayouts
 * @since 2.1.0
 */
class FieldLayouts extends Component
{

    /**
     * @var FieldLayout[]|null All field layouts of Ads, indexed by ID.
     */
    private ?array $_fieldLayoutsById = null;

    /**
     * @var bool Whether the field layouts have already been fetched.
     */
    private bool $_fetchedAllFieldLayouts = false;

    /**
     * Returns all field layouts.
     *
     * @return FieldLayouts[]
     */
    public function getFieldLayouts(): array
    {
        // If we've already fetched the layouts, return them
        if ($this->_fetchedAllFieldLayouts) {
            return array_values($this->_fieldLayoutsById);
        }

        // Initialize field layouts
        $this->_fieldLayoutsById = [];

        // Get all valid layout IDs
        $layoutIds = (new Query())
            ->select(['id'])
            ->from([Table::FIELDLAYOUTS])
            ->where(['type' => Ad::class])
            ->andWhere(['dateDeleted' => null])
            ->column();

        /** @var FieldLayoutRecord[] $fieldLayoutRecords */
        $fieldLayoutRecords = FieldLayoutRecord::find()
            ->where(['in', 'id', $layoutIds])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        // Loop through records
        foreach ($fieldLayoutRecords as $layoutRecord) {
            $this->_fieldLayoutsById[$layoutRecord->id] = $this->_createFieldLayoutFromRecord($layoutRecord);
        }

        // Mark as fetched
        $this->_fetchedAllFieldLayouts = true;

        // Return layouts
        return array_values($this->_fieldLayoutsById);
    }

    /**
     * Returns a field layout by its ID.
     *
     * @param int $layoutId
     * @return FieldLayout|null
     */
    public function getLayoutById(int $layoutId): ?FieldLayout
    {
        if ($this->_fieldLayoutsById !== null && isset($this->_fieldLayoutsById[$layoutId])) {
            return $this->_fieldLayoutsById[$layoutId];
        }

        if ($this->_fetchedAllFieldLayouts) {
            return null;
        }

        $layoutRecord = FieldLayoutRecord::findOne([
            'id' => $layoutId,
        ]);

        if ($layoutRecord === null) {
            return $this->_fieldLayoutsById[$layoutId] = null;
        }

        /** @var FieldLayoutRecord $layoutRecord */
        return $this->_fieldLayoutsById[$layoutId] = $this->_createFieldLayoutFromRecord($layoutRecord);
    }

    /**
     * Saves a field layout.
     *
     * @param CraftFieldLayout $layout
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function saveLayout(CraftFieldLayout $layout, $name): bool
    {
        if (!$layout->validate()) {
            Craft::info('Field layout not saved due to validation error.', __METHOD__);
            return false;
        }

        $layoutRecord = FieldLayoutRecord::findOne($layout->id);

        // If no existing record, create one
        if (!$layoutRecord) {
            $layoutRecord = new FieldLayoutRecord();
            $layoutRecord->id = $layout->id;
        }

        $layoutRecord->name = $name;

        /** @var Transaction $transaction */
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Save it!
            $layoutRecord->save(false);

            // Now that we have a field layout ID, save it on the model
            if (!$layout->id) {
                $layout->id = $layoutRecord->id;
            }

            // Might as well update our cache of the layout while we have it.
            $this->_fieldLayoutsById[$layout->id] = $layout;

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Deletes a field layout by its ID.
     *
     * @param int $fieldLayoutId
     * @return bool Whether the layout was deleted successfully.
     * @throws Throwable
     */
    public function deleteLayoutById(int $fieldLayoutId): bool
    {
        if (!$fieldLayoutId) {
            return false;
        }

        /** @var Transaction $transaction */
        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            // Delete the field layout
            $success = Craft::$app->getFields()->deleteLayoutById($fieldLayoutId);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return ($success ?? false);
    }

    // ========================================================================= //

    /**
     * Creates a FieldLayout with attributes from an FieldLayoutRecord.
     *
     * @param FieldLayoutRecord|null $layoutRecord
     * @return FieldLayout|null
     */
    private function _createFieldLayoutFromRecord(?FieldLayoutRecord $layoutRecord): ?FieldLayout
    {
        if (!$layoutRecord) {
            return null;
        }

        $layout = new FieldLayout($layoutRecord->toArray([
            'id',
            'name',
        ]));

        return $layout;
    }

}
