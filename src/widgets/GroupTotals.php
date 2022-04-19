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
use doublesecretagency\adwizard\models\AdGroup;
use doublesecretagency\adwizard\web\assets\GroupTotalsAssets;
use doublesecretagency\adwizard\web\assets\WidgetAssets;

/**
 * Class GroupTotals
 * @since 2.0.0
 */
class GroupTotals extends Widget
{

    /**
     * @var int|null ID of the ad group.
     */
    public ?int $groupId = null;

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
    public static function icon(): ?string
    {
        return Craft::getAlias('@doublesecretagency/adwizard/ad-totals.svg');
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        // Set default title
        $title = Craft::t('ad-wizard', 'New group chart');

        // No ID, bail with default title
        if (!$this->groupId) {
            return $title;
        }

        /** @var AdGroup $group */
        $group = AdWizard::$plugin->groups->getGroupById($this->groupId);

        // Return name of group or default title
        return ($group->name ?? $title);
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml(): ?string
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
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('ad-wizard/widgets/settings/ad-totals', [
            'widget' => $this,
        ]);
    }

}
