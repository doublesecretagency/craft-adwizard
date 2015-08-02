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

	// ============================================================== //

	// Get month total of views
	public function monthTotalViews($id, $year, $month)
	{
		return craft()->adWizard->monthTotalViews($id, $year, $month);
	}

	// Get month total of clicks
	public function monthTotalClicks($id, $year, $month)
	{
		return craft()->adWizard->monthTotalClicks($id, $year, $month);
	}

}