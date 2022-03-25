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

use craft\base\Model;

/**
 * Class Settings
 * @since 2.1.0
 */
class Settings extends Model
{
    public $enableAdUrls = true;
    public $enableAdImages = true;
}
