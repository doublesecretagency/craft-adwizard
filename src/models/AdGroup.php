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
use craft\models\FieldLayout;

/**
 * Class AdGroup
 * @since 2.0.0
 */
class AdGroup extends Model
{

    /**
     * @var int|null $id ID of ad group.
     */
    public ?int $id = null;

    /**
     * @var int|null $fieldLayoutId ID of group's field layout.
     */
    public ?int $fieldLayoutId = null;

    /**
     * @var string|null $name Name of ad group.
     */
    public ?string $name = null;

    /**
     * @var string|null $handle Handle of ad group.
     */
    public ?string $handle = null;

    /**
     * Use the translated group name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return Craft::t('site', $this->name);
    }

    /**
     * Get the field layout of this Ad Group.
     *
     * @return FieldLayout|null
     */
    public function getFieldLayout(): ?FieldLayout
    {
        // If no field layout ID
        if (!$this->fieldLayoutId) {
            // Return a new field layout
            return new FieldLayout();
        }

        // Return the specified field layout
        return Craft::$app->getFields()->getLayoutById($this->fieldLayoutId);
    }

}
