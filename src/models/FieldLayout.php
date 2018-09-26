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

namespace doublesecretagency\adwizard\models;

use Craft;
use craft\base\Model;

/**
 * Class FieldLayout
 * @since 2.1.0
 */
class FieldLayout extends Model
{

    /** @var int  $id  ID of field layout. */
    public $id;

    /** @var string  $name  Name of field layout. */
    public $name;

    /**
     * Use the translated layout name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) Craft::t('site', $this->name);
    }

    public function getFieldLayout()
    {
        if (!$this->id) {
            return new \craft\models\FieldLayout();
        }
        return Craft::$app->getFields()->getLayoutById($this->id);
    }

}
