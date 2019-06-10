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
use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\web\assets\AdTimelineAssets;
use doublesecretagency\adwizard\web\assets\AdTimelineSettingsAssets;
use doublesecretagency\adwizard\web\assets\WidgetAssets;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;

/**
 * Class AdTimeline
 * @since 2.0.0
 */
class AdTimeline extends Widget
{
    // Static
    // =========================================================================

    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('ad-wizard', 'Ad Timeline');
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getTitle(): string
    {
        // Set default title
        $title = Craft::t('ad-wizard', 'New ad timeline');

        // No ID, bail with default title
        if (!$this->adId) {
            return $title;
        }

        // Get ad
        /** @var Ad $ad */
        $ad = AdWizard::$plugin->ads->getAdById($this->adId);

        // No ad, bail with default title
        if (!$ad) {
            return $title;
        }

        // Return title of ad
        return $ad->title;
    }

    /**
     * @inheritDoc
     * @return false|string
     * @throws InvalidConfigException
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
     * @inheritDoc
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws InvalidConfigException
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
