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
 * Class AdGroupSwitcherAssets
 * @since 2.1.0
 */
class AdGroupSwitcherAssets extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = '@doublesecretagency/adwizard/resources';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/garnish/AdGroupSwitcher.js',
        ];

        parent::init();
    }

}
