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

namespace doublesecretagency\adwizard\web\assets;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class VueAssets
 * @since 2.1.0
 */
class VueAssets extends AssetBundle
{

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->sourcePath = '@doublesecretagency/adwizard/resources';

        $this->depends = [
            CpAsset::class,
        ];

        // If "devMode" is enabled
        if (Craft::$app->getConfig()->getGeneral()->devMode) {
            // Development mode
            $vue = 'vue.js';
        } else {
            // Production mode
            $vue = 'vue.min.js';
        }

        $this->js = [
            "js/{$vue}",
        ];

        parent::init();
    }

}
