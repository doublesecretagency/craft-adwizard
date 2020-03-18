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

namespace doublesecretagency\adwizard\fields;

use Craft;
use craft\base\ElementInterface;
use craft\fields\BaseRelationField;
use doublesecretagency\adwizard\elements\Ad;

class Ads extends BaseRelationField
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ad-wizard', 'Ads');
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('ad-wizard', 'Add an ad');
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return Ad::class;
    }
}
