<?php
namespace Craft;

class AdWizard_ClickRecord extends BaseRecord
{
    
    public function getTableName()
    {
        return 'adwizard_clicks';
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