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

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class AdminAssets
 * @since 2.0.0
 */
class AdminAssets extends AssetBundle
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

        $this->css = [
            'css/admin.css',
        ];

        $this->js = [
            'js/admin.js',
        ];

        parent::init();
    }

}
