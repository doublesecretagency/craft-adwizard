<?php
namespace Craft;

class AdWizard_PositionTotalsWidget extends BaseWidget
{

	public function getTitle()
	{
		$positionId = $this->getSettings()->positionId;
		if ($positionId) {
			$position = craft()->adWizard->getPositionById($positionId);
			$title = $position->name;
		} else {
			$title = Craft::t('NEW POSITION CHART');
		}
		return $title;
	}

	public function getName()
	{
		return Craft::t('Ad Totals');
	}

	public function getIconPath()
	{
		return craft()->path->getPluginsPath().'adwizard/resources/ad-totals.svg';
	}

	public function getBodyHtml()
	{
		$positionId = $this->getSettings()->positionId;
		$chart = craft()->adWizard_widget->positionBarChart($positionId);
		$intro = '<p>Lifetime totals of ads in this group.</p>';
		return $intro.'<p>'.$chart.'</p>';
	}

	protected function defineSettings()
	{
		return array(
			'positionId' => array(AttributeType::Number),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('adwizard/_settings/widgets/ad-totals', array(
			'settings' => $this->getSettings(),
			'pluginsPath' => craft()->path->getPluginsPath(),
		));
	}

}