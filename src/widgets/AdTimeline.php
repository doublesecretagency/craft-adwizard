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

namespace doublesecretagency\adwizard\widgets;

use Craft;
use craft\base\Widget;

use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\web\assets\AdTimelineAssets;
use doublesecretagency\adwizard\web\assets\AdTimelineSettingsAssets;
use doublesecretagency\adwizard\web\assets\WidgetAssets;

/**
 * Class AdTimeline
 * @since 2.0.0
 */
class AdTimeline extends Widget
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ad-wizard', 'Ad Timeline');
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias('@doublesecretagency/adwizard/ad-timeline.svg');
    }

    // Properties
    // =========================================================================

    /**
     * @var int|null The ID of the ad group
     */
    public $groupId;

    /**
     * @var int|null The ID of the ad
     */
    public $adId;


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        $title = Craft::t('ad-wizard', 'New ad timeline');
        if ($this->adId) {
            $ad = AdWizard::$plugin->adWizard_ads->getAdById($this->adId);
            if ($ad) {
                $title = $ad->title;
            }
        }
        return $title;
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml()
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(WidgetAssets::class);
        $view->registerAssetBundle(AdTimelineAssets::class);
        $view->registerJs('new Craft.AdTimelineWidget('.$this->id.', '.$this->adId.');');
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(AdTimelineSettingsAssets::class);
        return $view->renderTemplate('ad-wizard/widgets/settings/ad-timeline', [
            'widget' => $this,
        ]);
    }
}
