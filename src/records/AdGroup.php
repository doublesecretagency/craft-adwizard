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

use yii\db\ActiveQueryInterface;

use craft\db\ActiveRecord;

/**
 * Class AdGroup
 * @since 2.0.0
 */
class AdGroup extends ActiveRecord
{

    /**
     * @inheritdoc
     *
     * @return string
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
