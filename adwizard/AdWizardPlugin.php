<?php
namespace Craft;

class AdWizardPlugin extends BasePlugin
{

    public function getName()
    {
        return Craft::t('Ad Wizard');
    }

    public function getVersion()
    {
        return '1.0.1';
    }

    public function getDeveloper()
    {
        return 'Double Secret Agency';
    }

    public function getDeveloperUrl()
    {
        return 'https://craftpl.us/plugins/ad-wizard';
        //return 'http://doublesecretagency.com';
    }

    public function hasCpSection()
    {
        return true;
    }

    public function registerCpRoutes()
    {
        return array(
            'adwizard'                                            => array('action' => 'adWizard/adIndex'),
            'adwizard/positions'                                  => array('action' => 'adWizard/positionIndex'),
            'adwizard/positions/new'                              => array('action' => 'adWizard/editPosition'),
            'adwizard/positions/(?P<positionId>\d+)'              => array('action' => 'adWizard/editPosition'),
            'adwizard/(?P<positionHandle>{handle})/new'           => array('action' => 'adWizard/editAd'),
            'adwizard/(?P<positionHandle>{handle})/(?P<adId>\d+)' => array('action' => 'adWizard/editAd'),
        );
    }

    public function onAfterInstall()
    {
        // @TODO: Change to "Introduction" page
        craft()->request->redirect(UrlHelper::getCpUrl('adwizard/thanks'));
    }

    /*
    public function getSettingsHtml()
    {
        return craft()->templates->render('adwizard/_settings', array(
            'settings' => $this->getSettings()
        ));
    }
    */

    /*
    protected function defineSettings()
    {
        return array(
            'assetSourceId' => array(AttributeType::Number, 'label' => 'Ad Wizard Assets Source'),
        );
    }
    */
    
}
