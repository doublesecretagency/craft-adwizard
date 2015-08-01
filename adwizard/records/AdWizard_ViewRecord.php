<?php
namespace Craft;

class AdWizard_ViewRecord extends BaseRecord
{
	
	public function getTableName()
	{
		return 'adwizard_views';
	}

	protected function defineAttributes()
	{
		return craft()->adWizard->trackingAttributes();
	}

	public function defineRelations()
	{
		return craft()->adWizard->trackingRelations();
	}

}