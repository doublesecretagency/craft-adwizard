<?php
namespace Craft;

class AdWizardVariable
{

    // Display specified ad
    public function ad($id)
    {
        return craft()->adWizard->renderAd($id);
    }

    // Display random ad from specified position
    public function position($position)
    {
        return craft()->adWizard->renderAdFromPosition($position);
    }
    
    // ============================================================== //

    // List all ads
    public function allAds()
    {
        return craft()->adWizard->getAllAds();
    }

    // List all positions
    public function allPositions()
    {
        return craft()->adWizard->getAllPositions();
    }
    
}