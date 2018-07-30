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
 * Class Click
 * @since 2.0.0
 */
class Click extends ActiveRecord
{

    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%adwizard_clicks}}';
    }

    /**
     * Returns the ad of tracking info.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getAd(): ActiveQueryInterface
    {
        return $this->hasOne(Ad::class, ['id' => 'adId']);
    }

}
