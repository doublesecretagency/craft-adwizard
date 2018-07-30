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
use doublesecretagency\adwizard\web\assets\GroupTotalsAssets;
use doublesecretagency\adwizard\web\assets\WidgetAssets;

/**
 * Class GroupTotals
 * @since 2.0.0
 */
class GroupTotals extends Widget
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ad-wizard', 'Ad Group Totals');
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias('@doublesecretagency/adwizard/ad-totals.svg');
    }

    // Properties
    // =========================================================================

    /**
     * @var int|null The ID of the ad group
     */
    public $groupId;


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        $title = Craft::t('ad-wizard', 'New group chart');
        if ($this->groupId) {
            $group = AdWizard::$plugin->adWizard_groups->getGroupById($this->groupId);
            if ($group) {
                $title = $group->name;
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
        $view->registerAssetBundle(GroupTotalsAssets::class);
        $view->registerJs('new Craft.GroupTotalsWidget('.$this->id.', '.$this->groupId.');');
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('ad-wizard/widgets/settings/ad-totals', [
            'widget' => $this,
        ]);
    }
}
