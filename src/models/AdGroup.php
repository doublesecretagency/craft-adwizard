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
 * Class AdGroup
 * @since 2.0.0
 */
class AdGroup extends Model
{

    /**
     * @var int $id ID of ad group.
     */
    public $id;

    /**
     * @var int $fieldLayoutId ID of group's field layout.
     */
    public $fieldLayoutId;

    /**
     * @var string $name Name of ad group.
     */
    public $name;

    /**
     * @var string $handle Handle of ad group.
     */
    public $handle;

    /**
     * Use the translated group name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) Craft::t('site', $this->name);
    }

}
