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

namespace doublesecretagency\adwizard\records;

use craft\db\ActiveRecord;
use craft\records\Asset;
use craft\records\Element;
use DateTime;
use yii\db\ActiveQueryInterface;

/**
 * Class Ad
 * @since 2.0.0
 *
 * @property int $id
 * @property int $groupId
 * @property int $assetId
 * @property string $url
 * @property DateTime $startDate
 * @property DateTime $endDate
 * @property int $maxViews
 * @property int $totalViews
 * @property int $totalClicks
 * @property DateTime $dateCreated
 * @property DateTime $dateUpdated
 * @property string $uid
 *
 * @property ActiveQueryInterface $element Ad element.
 * @property ActiveQueryInterface $group Group ad belongs to.
 * @property ActiveQueryInterface $asset Image asset related to this ad.
 */
class Ad extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%adwizard_ads}}';
    }

    /**
     * Returns the ad’s element.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }

    /**
     * Returns the ad’s group.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getGroup(): ActiveQueryInterface
    {
        return $this->hasOne(AdGroup::class, ['id' => 'groupId']);
    }

    /**
     * Returns the ad’s asset.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getAsset(): ActiveQueryInterface
    {
        return $this->hasOne(Asset::class, ['id' => 'assetId']);
    }

}
