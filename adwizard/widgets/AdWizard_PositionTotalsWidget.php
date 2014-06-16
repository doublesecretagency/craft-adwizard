<?php
namespace Craft;

class AdWizard_PositionTotalsWidget extends BaseWidget
{
    protected $colspan = 2;

    public function getName()
    {
        return Craft::t('Ad Wizard - Position Totals');
    }

    public function getBodyHtml()
    {
        $positionId = $this->getSettings()->positionId;
        return craft()->adWizard_widget->positionBarChart($positionId);
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