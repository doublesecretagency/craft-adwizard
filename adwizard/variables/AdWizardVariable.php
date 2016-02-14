<?php
namespace Craft;

class AdWizardVariable
{

	// Display specified ad
	public function displayAd($id, $transform = null, $retina = false)
	{
		return craft()->adWizard->renderAd($id, $transform, $retina);
	}

	// Display random ad from specified ad group
	public function randomizeAdGroup($group, $transform = null, $retina = false)
	{
		return craft()->adWizard->renderRandomAdFromGroup($group, $transform, $retina);
	}

	// ============================================================== //

	// Get all Ad elements
	public function ads($criteria = null)
	{
		return craft()->elements->getCriteria('AdWizard_Ad', $criteria);
	}

	// ============================================================== //

	// Get all groups
	public function getAllGroups()
	{
		return craft()->adWizard->getAllGroups();
	}

	// Get group by id
	public function getGroupById($groupId)
	{
		return craft()->adWizard->getGroupById($groupId);
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

	// ============================================================== //
	// ============================================================== //

	// DEPRECATED
	public function ad($id, $transform = null, $retina = false)
	{
		return $this->displayAd($id, $transform, $retina);
	}

	// DEPRECATED
	public function position($group, $transform = null, $retina = false)
	{
		return $this->randomizeAdGroup($group, $transform, $retina);
	}

}