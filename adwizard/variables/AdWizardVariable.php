<?php
namespace Craft;

class AdWizardVariable
{

    // Display specified ad
    public function ad($id, $transform = null)
    {
        return craft()->adWizard->renderAd($id, $transform);
    }

    // Display random ad from specified position
    public function position($position, $transform = null)
    {
        return craft()->adWizard->renderAdFromPosition($position, $transform);
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