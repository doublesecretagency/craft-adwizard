<?php
namespace Craft;

class AdWizard_PositionTotalsWidget extends BaseWidget
{

	protected $colspan = 2;

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
		return Craft::t('Ad Position Totals');
	}

	public function getBodyHtml()
	{
		$positionId = $this->getSettings()->positionId;
		$chart = craft()->adWizard_widget->positionBarChart($positionId);
		$intro = '<h3>Lifetime totals</h3>';
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
		return craft()->templates->render('adwizard/_settings/widgets/positiontotals', array(
			'settings' => $this->getSettings(),
		));
	}

}