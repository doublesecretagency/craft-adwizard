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

	public function getVersion()
	{
		return '1.3.2';
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
		return 'https://www.doublesecretagency.com/plugins';
	}

	public function getDocumentationUrl()
	{
		return 'https://www.doublesecretagency.com/plugins/ad-wizard/docs';
	}

	public function hasCpSection()
	{
		return true;
	}

	public function addAdWizard_AdActions($source)
	{
		return array(
			'AdWizard_ChangeAdGroup',
		);
	}

	public function registerCpRoutes()
	{
		return array(
			'adwizard/ads'                                     => array('action' => 'adWizard/adIndex'),
			'adwizard/groups'                                  => array('action' => 'adWizard/groupIndex'),
			'adwizard/groups/new'                              => array('action' => 'adWizard/editGroup'),
			'adwizard/groups/(?P<groupId>\d+)'                 => array('action' => 'adWizard/editGroup'),
			'adwizard/ads/new'                                 => array('action' => 'adWizard/editAd'),
			'adwizard/(?P<groupHandle>{handle})/new'           => array('action' => 'adWizard/editAd'),
			'adwizard/(?P<groupHandle>{handle})/(?P<adId>\d+)' => array('action' => 'adWizard/editAd'),
		);
	}

	public function onAfterInstall()
	{
		craft()->request->redirect(UrlHelper::getCpUrl('adwizard/welcome'));
	}

}
