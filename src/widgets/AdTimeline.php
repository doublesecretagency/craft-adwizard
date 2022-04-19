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

/**
 * Class AdTimeline
 * @since 2.0.0
 */
class AdTimeline extends Widget
{

    /**
     * @var int|null ID of the ad.
     */
    public ?int $adId = null;

    /**
     * @var int|null ID of the ad group.
     */
    public ?int $groupId = null;

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
    public static function icon(): ?string
    {
        return Craft::getAlias('@doublesecretagency/adwizard/ad-timeline.svg');
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        // Set default title
        $title = Craft::t('ad-wizard', 'New ad timeline');

        // No ID, bail with default title
        if (!$this->adId) {
            return $title;
        }

        /** @var Ad $ad */
        $ad = AdWizard::$plugin->ads->getAdById($this->adId);

        // Return title of ad or default title
        return ($ad->title ?? $title);
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml(): ?string
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
    public function getSettingsHtml(): ?string
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(AdTimelineSettingsAssets::class);
        return $view->renderTemplate('ad-wizard/widgets/settings/ad-timeline', [
            'widget' => $this,
        ]);
    }

}
