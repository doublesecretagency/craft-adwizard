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
 * Class Click
 * @since 2.0.0
 *
 * @property int $id
 * @property int $adId
 * @property int $year
 * @property int $month
 * @property int $day1
 * @property int $day2
 * @property int $day3
 * @property int $day4
 * @property int $day5
 * @property int $day6
 * @property int $day7
 * @property int $day8
 * @property int $day9
 * @property int $day10
 * @property int $day11
 * @property int $day12
 * @property int $day13
 * @property int $day14
 * @property int $day15
 * @property int $day16
 * @property int $day17
 * @property int $day18
 * @property int $day19
 * @property int $day20
 * @property int $day21
 * @property int $day22
 * @property int $day23
 * @property int $day24
 * @property int $day25
 * @property int $day26
 * @property int $day27
 * @property int $day28
 * @property int $day29
 * @property int $day30
 * @property int $day31
 * @property int $total
 * @property DateTime $dateCreated
 * @property DateTime $dateUpdated
 * @property string $uid
 *
 * @property ActiveQueryInterface $ad The ad reflected by this tracking info.
 */
class Click extends ActiveRecord
{

    /**
     * @inheritdoc
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
