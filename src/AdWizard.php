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

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Dashboard;
use craft\services\Fields;
use craft\services\Plugins;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use doublesecretagency\adwizard\fields\Ads as AdsField;
use doublesecretagency\adwizard\fields\AdGroups as AdGroupsField;
use doublesecretagency\adwizard\services\AdGroups;
use doublesecretagency\adwizard\services\Ads;
use doublesecretagency\adwizard\services\FieldLayouts;
use doublesecretagency\adwizard\services\Tracking;
use doublesecretagency\adwizard\services\Widgets;
use doublesecretagency\adwizard\variables\AdWizardVariable;
use doublesecretagency\adwizard\widgets\AdTimeline;
use doublesecretagency\adwizard\widgets\GroupTotals;
use yii\base\Event;

/**
 * Class AdWizard
 * @since 2.0.0
 *
 * @property Ads $ads
 * @property AdGroups $groups
 * @property FieldLayouts $fieldLayouts
 * @property Tracking $tracking
 * @property Widgets $widgets
 */
class AdWizard extends Plugin
{

    /**
     * @const Root URL for documentation.
     */
    public const DOCS_URL = 'https://plugins.doublesecretagency.com/ad-wizard/';

    /**
     * @var AdWizard $plugin Self-referential plugin property.
     */
    public static AdWizard $plugin;

    /**
     * @var bool $hasCpSection The plugin has a section with subpages.
     */
    public bool $hasCpSection = true;

    /**
     * @var bool $schemaVersion Current schema version of the plugin.
     */
    public string $schemaVersion = '2.1.0';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Load plugin components
        $this->setComponents([
            'ads'          => Ads::class,
            'groups'       => AdGroups::class,
            'fieldLayouts' => FieldLayouts::class,
            'tracking'     => Tracking::class,
            'widgets'      => Widgets::class,
        ]);

        // Register variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            static function (Event $event) {
                $variable = $event->sender;
                $variable->set('adWizard', AdWizardVariable::class);
            }
        );

        // Register widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            static function(RegisterComponentTypesEvent $event) {
                $event->types[] = AdTimeline::class;
                $event->types[] = GroupTotals::class;
            }
        );

        // Register fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            static function(RegisterComponentTypesEvent $event) {
                $event->types[] = AdsField::class;
                $event->types[] = AdGroupsField::class;
            }
        );

        // Register CP site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function(RegisterUrlRulesEvent $event) {
                // Field Layouts
                $event->rules['ad-wizard/fieldlayouts']                     = 'ad-wizard/field-layouts';
                $event->rules['ad-wizard/fieldlayouts/new']                 = 'ad-wizard/field-layouts/edit-field-layout';
                $event->rules['ad-wizard/fieldlayouts/<fieldLayoutId:\d+>'] = 'ad-wizard/field-layouts/edit-field-layout';
                // Groups
                $event->rules['ad-wizard/groups']               = 'ad-wizard/ad-groups';
                $event->rules['ad-wizard/groups/new']           = 'ad-wizard/ad-groups/edit-ad-group';
                $event->rules['ad-wizard/groups/<groupId:\d+>'] = 'ad-wizard/ad-groups/edit-ad-group';
                // Ads
                $event->rules['ad-wizard/ads']                                   = 'ad-wizard/ads';
                $event->rules['ad-wizard/ads/new']                               = 'ad-wizard/ads/edit-ad';
                $event->rules['ad-wizard/ads/<groupHandle:{handle}>']            = 'ad-wizard/ads';
                $event->rules['ad-wizard/ads/<groupHandle:{handle}>/new']        = 'ad-wizard/ads/edit-ad';
                $event->rules['ad-wizard/ads/<groupHandle:{handle}>/<adId:\d+>'] = 'ad-wizard/ads/edit-ad';
            }
        );

        // Redirect to welcome page after install
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            static function (Event $event) {

                // If console request, bail
                if (Craft::$app->getRequest()->getIsConsoleRequest()) {
                    return;
                }

                // If not Ad Wizard, bail
                if ('ad-wizard' !== $event->plugin->handle) {
                    return;
                }

                // Redirect to the welcome page
                $url = UrlHelper::cpUrl('ad-wizard/welcome');
                Craft::$app->getResponse()->redirect($url)->send();
            }
        );

    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        $item['subnav'] = [];

        $groupsExist = $this->groups->getAllGroups();

        $item['subnav']['stats'] = ['label' => 'Stats', 'url' => 'ad-wizard/stats'];
        if ($groupsExist) {
            $item['subnav']['ads'] = ['label' => 'Ads', 'url' => 'ad-wizard/ads'];
        }
        $item['subnav']['groups'] = ['label' => 'Groups', 'url' => 'ad-wizard/groups'];
        $item['subnav']['fieldlayouts'] = ['label' => 'Field Layouts', 'url' => 'ad-wizard/fieldlayouts'];

        return $item;
    }

}
