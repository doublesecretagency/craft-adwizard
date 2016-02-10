<?php
namespace Craft;

class AdWizard_AdTimelineWidget extends BaseWidget
{

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

	public function getIconPath()
	{
		return craft()->path->getPluginsPath().'adwizard/resources/ad-timeline.svg';
	}

	public function getBodyHtml()
	{
		$adId = $this->getSettings()->adId;
		$chart = craft()->adWizard_widget->adLineChart($adId);
		$intro = '<p>Ad activity from this month.</p>';
		return $intro.'<p>'.$chart.'</p>';
	}

	protected function defineSettings()
	{
		return array(
			'groupId' => array(AttributeType::Number),
			'adId'    => array(AttributeType::Number),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('adwizard/_settings/widgets/ad-timeline', array(
			'settings' => $this->getSettings(),
			'pluginsPath' => craft()->path->getPluginsPath(),
		));
	}

}