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

namespace doublesecretagency\adwizard;

use yii\base\Event;

use Craft;
use craft\base\Plugin;
use craft\helpers\UrlHelper;
use craft\services\Dashboard;
use craft\services\Plugins;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;

use doublesecretagency\adwizard\services\Ads;
use doublesecretagency\adwizard\services\AdGroups;
use doublesecretagency\adwizard\services\FieldLayouts;
use doublesecretagency\adwizard\services\Tracking;
use doublesecretagency\adwizard\services\Widgets;
use doublesecretagency\adwizard\variables\AdWizardVariable;
use doublesecretagency\adwizard\widgets\AdTimeline;
use doublesecretagency\adwizard\widgets\GroupTotals;

/**
 * Class AdWizard
 * @since 2.0.0
 */
class AdWizard extends Plugin
{

    /** @var Plugin  $plugin  Self-referential plugin property. */
    public static $plugin;

    /** @var bool  $hasCpSection  The plugin has a section with subpages. */
    public $hasCpSection = true;

    /** @var bool  $schemaVersion  Current schema version of the plugin. */
    public $schemaVersion = '2.1.0-alpha.1';

    /** @inheritDoc */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Load plugin components
        $this->setComponents([
            'adWizard_ads'          => Ads::class,
            'adWizard_groups'       => AdGroups::class,
            'adWizard_fieldLayouts' => FieldLayouts::class,
            'adWizard_tracking'     => Tracking::class,
            'adWizard_widgets'      => Widgets::class,
        ]);

        // Register variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $variable = $event->sender;
                $variable->set('adWizard', AdWizardVariable::class);
            }
        );

        // Register widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = AdTimeline::class;
                $event->types[] = GroupTotals::class;
            }
        );

        // Register CP site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                // Field Layouts
                $event->rules['ad-wizard/fieldlayouts']                     = 'ad-wizard/field-layouts';
                $event->rules['ad-wizard/fieldlayouts/new']                 = 'ad-wizard/field-layouts/edit-field-layout';
                $event->rules['ad-wizard/fieldlayouts/<fieldLayoutId:\d+>'] = 'ad-wizard/field-layouts/edit-field-layout';
                // Groups
                $event->rules['ad-wizard/groups']               = 'ad-wizard/ad-groups';
                $event->rules['ad-wizard/groups/new']           = 'ad-wizard/ad-groups/edit-ad-group';
                $event->rules['ad-wizard/groups/<groupId:\d+>'] = 'ad-wizard/ad-groups/edit-ad-group';
                // Ads
                $event->rules['ad-wizard/ads']                               = 'ad-wizard/ads';
                $event->rules['ad-wizard/ads/new']                           = 'ad-wizard/ads/edit-ad';
                $event->rules['ad-wizard/<groupHandle:{handle}>/new']        = 'ad-wizard/ads/edit-ad';
                $event->rules['ad-wizard/<groupHandle:{handle}>/<adId:\d+>'] = 'ad-wizard/ads/edit-ad';
            }
        );

        // Redirect to welcome page after install
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (Event $event) {
                if ('ad-wizard' == $event->plugin->handle) {
                    $url = UrlHelper::cpUrl('ad-wizard/welcome');
                    Craft::$app->getResponse()->redirect($url)->send();
                }
            }
        );

    }

    /** @inheritDoc */
    public function getCpNavItem()
    {
        $item = parent::getCpNavItem();
        $item['subnav'] = [];

        $groupsExist = $this->adWizard_groups->getAllGroups();

        $item['subnav']['stats'] = ['label' => 'Stats', 'url' => 'ad-wizard/stats'];
        if ($groupsExist) {
            $item['subnav']['ads'] = ['label' => 'Ads', 'url' => 'ad-wizard/ads'];
        }
        $item['subnav']['groups'] = ['label' => 'Groups', 'url' => 'ad-wizard/groups'];
        $item['subnav']['fieldlayouts'] = ['label' => 'Field Layouts', 'url' => 'ad-wizard/fieldlayouts'];

        return $item;
    }

}
