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

use DateTime;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Class AdGroup
 * @since 2.0.0
 *
 * @property int $id
 * @property int $fieldLayoutId
 * @property string $name
 * @property string $handle
 * @property DateTime $dateCreated
 * @property DateTime $dateUpdated
 * @property string $uid
 *
 * @property ActiveQueryInterface $ads All ads in this group.
 */
class AdGroup extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%adwizard_groups}}';
    }

    /**
     * Returns the ad group’s ads.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getAds(): ActiveQueryInterface
    {
        return $this->hasMany(Ad::class, ['groupId' => 'id']);
    }

}
