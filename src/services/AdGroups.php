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

use yii\base\Exception;

use Craft;
use craft\base\Component;
use craft\db\Query;

use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\models\AdGroup;
use doublesecretagency\adwizard\records\AdGroup as AdGroupRecord;

/**
 * Class AdGroups
 * @since 2.0.0
 */
class AdGroups extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var int[]|null
     */
    private $_allGroupIds;

    /**
     * @var AdGroup[]|null
     */
    private $_adGroupsById;

    /**
     * @var bool
     */
    private $_fetchedAllAdGroups = false;

    // Public Methods
    // =========================================================================

    /**
     * Returns all of the group IDs.
     *
     * @return int[]
     */
    public function getAllGroupIds(): array
    {
        if ($this->_allGroupIds !== null) {
            return $this->_allGroupIds;
        }

        if ($this->_fetchedAllAdGroups) {
            return $this->_allGroupIds = array_keys($this->_adGroupsById);
        }

        return $this->_allGroupIds = (new Query())
            ->select(['id'])
            ->from(['{{%adwizard_groups}}'])
            ->column();
    }

    /**
     * Returns all groups.
     *
     * @return AdGroup[]
     */
    public function getAllGroups(): array
    {
        if ($this->_fetchedAllAdGroups) {
            return array_values($this->_adGroupsById);
        }

        $this->_adGroupsById = [];

        /** @var AdGroupRecord[] $groupRecords */
        $groupRecords = AdGroupRecord::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();

        foreach ($groupRecords as $groupRecord) {
            $this->_adGroupsById[$groupRecord->id] = $this->_createAdGroupFromRecord($groupRecord);
        }

        $this->_fetchedAllAdGroups = true;

        return array_values($this->_adGroupsById);
    }

    /**
     * Gets the total number of groups.
     *
     * @return int
     */
    public function getTotalGroups(): int
    {
        return count($this->getAllGroupIds());
    }

    /**
     * Returns an ad group by its ID.
     *
     * @param int $groupId
     * @return AdGroup|null
     */
    public function getGroupById(int $groupId)
    {
        if ($this->_adGroupsById !== null && array_key_exists($groupId, $this->_adGroupsById)) {
            return $this->_adGroupsById[$groupId];
        }

        if ($this->_fetchedAllAdGroups) {
            return null;
        }

        $groupRecord = AdGroupRecord::findOne([
            'id' => $groupId,
        ]);

        if ($groupRecord === null) {
            return $this->_adGroupsById[$groupId] = null;
        }

        /** @var AdGroupRecord $groupRecord */
        return $this->_adGroupsById[$groupId] = $this->_createAdGroupFromRecord($groupRecord);
    }

    /**
     * Returns an ad group by its handle.
     *
     * @param string $groupHandle
     * @return AdGroup|null
     */
    public function getGroupByHandle(string $groupHandle)
    {
        $groupRecord = AdGroupRecord::findOne([
            'handle' => $groupHandle
        ]);

        if ($groupRecord) {
            $group = $this->_createAdGroupFromRecord($groupRecord);
            $this->_adGroupsById[$group->id] = $group;

            return $group;
        }

        return null;
    }

    /**
     * Saves an ad group.
     *
     * @param AdGroup $group
     * @throws \Exception
     * @return bool
     */
    public function saveGroup(AdGroup $group): bool
    {
        $isNewGroup = !$group->id;

//        // Fire a 'beforeSaveAdGroup' event
//        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_GROUP)) {
//            $this->trigger(self::EVENT_BEFORE_SAVE_GROUP, new AdGroupEvent([
//                'adGroup' => $group,
//                'isNew' => $isNewGroup
//            ]));
//        }

        if (!$group->validate()) {
            Craft::info('Ad group not saved due to validation error.', __METHOD__);
            return false;
        }

        if (!$isNewGroup) {
            $groupRecord = AdGroupRecord::findOne($group->id);

            if (!$groupRecord) {
                throw new Exception("No group exists with the ID '{$group->id}'");
            }
        } else {
            $groupRecord = new AdGroupRecord();
        }

        $groupRecord->fieldLayoutId = $group->fieldLayoutId;
        $groupRecord->name          = $group->name;
        $groupRecord->handle        = $group->handle;

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Save it!
            $groupRecord->save(false);

            // Now that we have an ad group ID, save it on the model
            if (!$group->id) {
                $group->id = $groupRecord->id;
            }

            // Might as well update our cache of the group while we have it.
            $this->_adGroupsById[$group->id] = $group;

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

//        // Fire an 'afterSaveAdGroup' event
//        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_GROUP)) {
//            $this->trigger(self::EVENT_AFTER_SAVE_GROUP, new AdGroupEvent([
//                'adGroup' => $group,
//                'isNew' => $isNewGroup,
//            ]));
//        }

        return true;
    }

    /**
     * Deletes an ad group by its ID.
     *
     * @param int $groupId
     * @return bool Whether the group was deleted successfully
     * @throws \Throwable if reasons
     */
    public function deleteGroupById(int $groupId): bool
    {
        if (!$groupId) {
            return false;
        }

        $group = $this->getGroupById($groupId);

        if (!$group) {
            return false;
        }

//        // Fire a 'beforeDeleteAdGroup' event
//        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_GROUP)) {
//            $this->trigger(self::EVENT_BEFORE_DELETE_GROUP, new AdGroupEvent([
//                'adGroup' => $group
//            ]));
//        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            // Delete the ads
            $ads = Ad::find()
                ->status(null)
                ->groupId($groupId)
                ->all();

            foreach ($ads as $ad) {
                Craft::$app->getElements()->deleteElement($ad);
            }

            Craft::$app->getDb()->createCommand()
                ->delete(
                    '{{%adwizard_groups}}',
                    ['id' => $groupId])
                ->execute();

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

//        // Fire an 'afterDeleteAdGroup' event
//        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_GROUP)) {
//            $this->trigger(self::EVENT_AFTER_DELETE_GROUP, new AdGroupEvent([
//                'adGroup' => $group
//            ]));
//        }

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * Creates an AdGroup with attributes from an AdGroupRecord.
     *
     * @param AdGroupRecord|null $groupRecord
     * @return AdGroup|null
     */
    private function _createAdGroupFromRecord(AdGroupRecord $groupRecord = null)
    {
        if (!$groupRecord) {
            return null;
        }

        $group = new AdGroup($groupRecord->toArray([
            'id',
            'fieldLayoutId',
            'name',
            'handle',
        ]));

        return $group;
    }

}
