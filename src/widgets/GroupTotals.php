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
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;

/**
 * Class GroupTotals
 * @since 2.0.0
 */
class GroupTotals extends Widget
{

    // Static
    // =========================================================================

    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('ad-wizard', 'Ad Group Totals');
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getTitle(): string
    {
        // Set default title
        $title = Craft::t('ad-wizard', 'New group chart');

        // No ID, bail with default title
        if (!$this->groupId) {
            return $title;
        }

        // Get group
        $group = AdWizard::$plugin->groups->getGroupById($this->groupId);

        // No group, bail with default title
        if (!$group) {
            return $title;
        }

        // Return name of group
        return $group->name;
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
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
     * @inheritDoc
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('ad-wizard/widgets/settings/ad-totals', [
            'widget' => $this,
        ]);
    }

}
