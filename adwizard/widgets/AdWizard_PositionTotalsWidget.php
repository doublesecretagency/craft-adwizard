<?php
namespace Craft;

class AdWizard_PositionTotalsWidget extends BaseWidget
{
    protected $colspan = 2;

    public function getName()
    {
        return Craft::t('Ad Wizard - Totals');
    }

    public function getBodyHtml()
    {
        $position = $this->getSettings()->position;
        return craft()->adWizard_widget->positionBarChart($position);
    }

    protected function defineSettings()
    {
        return array(
           'position' => array(AttributeType::String),
        );
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('adwizard/_settings/widgets/positiontotals', array(
           'settings' => $this->getSettings(),
        ));
    }
}