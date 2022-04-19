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
use craft\models\FieldLayout as FieldLayoutModel;

/**
 * Class FieldLayout
 * @since 2.1.0
 *
 * @property FieldLayoutModel $fieldLayout
 */
class FieldLayout extends Model
{

    /**
     * @var int|null $id ID of field layout.
     */
    public ?int $id = null;

    /**
     * @var string|null $name Name of field layout.
     */
    public ?string $name = null;

    /**
     * Use the translated layout name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return Craft::t('site', $this->name);
    }

    /**
     * Get the field layout.
     *
     * @return FieldLayoutModel|null
     */
    public function getFieldLayout(): ?FieldLayoutModel
    {
        // If no field layout ID
        if (!$this->id) {
            // Return a new field layout
            return new FieldLayoutModel();
        }

        // Return the specified field layout
        return Craft::$app->getFields()->getLayoutById($this->id);
    }

}
