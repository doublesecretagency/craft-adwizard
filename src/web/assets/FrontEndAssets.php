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

/**
 * Class FrontEndAssets
 * @since 2.0.0
 */
class FrontEndAssets extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = '@doublesecretagency/adwizard/resources';

        $this->js = [
            'js/superagent.js',
            'js/aw.js',
        ];

        parent::init();
    }

}
