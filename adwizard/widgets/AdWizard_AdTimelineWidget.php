<?php
namespace Craft;

class AdWizard_AdTimelineWidget extends BaseWidget
{

	protected $colspan = 2;

	public function getName()
	{
		return Craft::t('Ad Timeline');
	}

	public function getBodyHtml()
	{
		$adId = $this->getSettings()->adId;
		return craft()->adWizard_widget->adLineChart($adId);
	}

	protected function defineSettings()
	{
		return array(
			'positionId' => array(AttributeType::Number),
			'adId'       => array(AttributeType::Number),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('adwizard/_settings/widgets/adtimeline', array(
		   'settings' => $this->getSettings(),
		));
	}

}