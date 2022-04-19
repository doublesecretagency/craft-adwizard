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
 * Class FieldLayout
 * @since 2.1.0
 *
 * @property int $id
 * @property string $name
 * @property DateTime $dateCreated
 * @property DateTime $dateUpdated
 * @property string $uid
 *
 * @property ActiveQueryInterface $fieldLayouts Groups using field layout.
 */
class FieldLayout extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%adwizard_fieldlayouts}}';
    }

    /**
     * Returns the field layout’s groups.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getFieldLayouts(): ActiveQueryInterface
    {
        return $this->hasMany(FieldLayout::class, ['fieldLayoutId' => 'id']);
    }

}
