<?php
namespace Craft;

class AdWizard_AdTimelineWidget extends BaseWidget
{
    protected $colspan = 2;

    public function getName()
    {
        return Craft::t('Ad Wizard - Timeline');
    }

    public function getBodyHtml()
    {
        $adId = $this->getSettings()->adId;
        return craft()->adWizard_widget->adLineChart($adId);
    }

    protected function defineSettings()
    {
        return array(
           'adId' => array(AttributeType::Number),
        );
    }

    public function getSettingsHtml()
    {
        $adId = $this->getSettings()->adId;
        return craft()->templates->render('adwizard/_settings/widgets/adtimeline', array(
           'settings' => $this->getSettings(),
           'position' => craft()->adWizard->parentPositionOfAd($adId),
        ));
    }
}