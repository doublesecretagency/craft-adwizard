<?php
namespace Craft;

class AdWizardPlugin extends BasePlugin
{

	public function getName()
	{
		return Craft::t('Ad Wizard');
	}

	public function getDescription()
	{
		return 'Easily manage custom advertisements on your website.';
	}

	public function getDocumentationUrl()
	{
		return 'https://craftpl.us/plugins/ad-wizard';
	}

	public function getVersion()
	{
		return '1.3.0 rc';
	}

	public function getSchemaVersion()
	{
		return '1.3.0';
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
			'adwizard/ads'                                        => array('action' => 'adWizard/adIndex'),
			'adwizard/positions'                                  => array('action' => 'adWizard/positionIndex'),
			'adwizard/positions/new'                              => array('action' => 'adWizard/editPosition'),
			'adwizard/positions/(?P<positionId>\d+)'              => array('action' => 'adWizard/editPosition'),
			'adwizard/(?P<positionHandle>{handle})/new'           => array('action' => 'adWizard/editAd'),
			'adwizard/(?P<positionHandle>{handle})/(?P<adId>\d+)' => array('action' => 'adWizard/editAd'),
		);
	}

	public function onAfterInstall()
	{
		craft()->request->redirect(UrlHelper::getCpUrl('adwizard/welcome'));
	}

}
