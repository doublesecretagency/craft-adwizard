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

use doublesecretagency\adwizard\models\FieldLayout;
use doublesecretagency\adwizard\records\FieldLayout as FieldLayoutRecord;

/**
 * Class FieldLayouts
 * @since 2.1.0
 */
class FieldLayouts extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var FieldLayout[]|null
     */
    private $_fieldLayoutsById;

    /**
     * @var bool
     */
    private $_fetchedAllFieldLayouts = false;

    // Public Methods
    // =========================================================================

    /**
     * Returns all field layouts.
     *
     * @return FieldLayouts[]
     */
    public function getFieldLayouts(): array
    {
        if ($this->_fetchedAllFieldLayouts) {
            return array_values($this->_fieldLayoutsById);
        }

        $this->_fieldLayoutsById = [];

        /** @var FieldLayoutRecord[] $fieldLayoutRecords */
        $fieldLayoutRecords = FieldLayoutRecord::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();

        foreach ($fieldLayoutRecords as $layoutRecord) {
            $this->_fieldLayoutsById[$layoutRecord->id] = $this->_createFieldLayoutFromRecord($layoutRecord);
        }

        $this->_fetchedAllFieldLayouts = true;

        return array_values($this->_fieldLayoutsById);
    }

    /**
     * Returns a field layout by its ID.
     *
     * @param int $layoutId
     * @return FieldLayout|null
     */
    public function getLayoutById(int $layoutId)
    {
        if ($this->_fieldLayoutsById !== null && array_key_exists($layoutId, $this->_fieldLayoutsById)) {
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
     * @param FieldLayout $layout
     * @throws \Exception
     * @return bool
     */
    public function saveFieldLayout(FieldLayout $layout): bool
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

        $layoutRecord->name = $layout->name;

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
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Deletes a field layout by its ID.
     *
     * @param int $fieldLayoutId
     * @return bool Whether the layout was deleted successfully
     * @throws \Throwable if reasons
     */
    public function deleteLayoutById(int $fieldLayoutId): bool
    {
        if (!$fieldLayoutId) {
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            // Delete the field layout
            Craft::$app->getFields()->deleteLayoutById($fieldLayoutId);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * Creates a FieldLayout with attributes from an FieldLayoutRecord.
     *
     * @param FieldLayoutRecord|null $layoutRecord
     * @return FieldLayout|null
     */
    private function _createFieldLayoutFromRecord(FieldLayoutRecord $layoutRecord = null)
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
