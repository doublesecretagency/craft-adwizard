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

    // Get all ads
    public function getAllAds()
    {
        return craft()->adWizard->getAllAds();
    }

    // Get all positions
    public function getAllPositions()
    {
        return craft()->adWizard->getAllPositions();
    }

    // Get position by id
    public function getPositionById($positionId)
    {
        return craft()->adWizard->getPositionById($positionId);
    }
    
}