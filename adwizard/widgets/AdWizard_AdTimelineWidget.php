<?php
namespace Craft;

class AdWizard_AdTimelineWidget extends BaseWidget
{

	protected $colspan = 2;

	public function getTitle()
	{
		$adId = $this->getSettings()->adId;
		if ($adId) {
			$ad = craft()->adWizard->getAdById($adId);
			$title = $ad->title;
		} else {
			$title = Craft::t('NEW TIMELINE');
		}
		return $title;
	}

	public function getName()
	{
		return Craft::t('Ad Timeline');
	}

	public function getBodyHtml()
	{
		$adId = $this->getSettings()->adId;
		$chart = craft()->adWizard_widget->adLineChart($adId);
		$intro = '<h3>Activity this month</h3>';
		return $intro.'<p>'.$chart.'</p>';
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