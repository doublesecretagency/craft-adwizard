<?php
namespace Craft;

/**
 * AdWizard - Ad model
 */
class AdWizard_AdModel extends BaseElementModel
{
    protected $elementType = 'AdWizard_Ad';

    /**
     * @access protected
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'positionId'  => AttributeType::Number,
            'assetId'     => AttributeType::Number,
            'url'         => AttributeType::Url,
            'details'     => AttributeType::String,
            'startDate'   => AttributeType::DateTime,
            'endDate'     => AttributeType::DateTime,
            'maxViews'    => AttributeType::Number,
            'totalViews'  => AttributeType::Number,
            'totalClicks' => AttributeType::Number,
            'filepath'    => AttributeType::String,
            'width'       => AttributeType::Number,
            'height'      => AttributeType::Number,
            'html'        => AttributeType::String,
        ));
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }

    /**
     * Returns the element's CP edit URL.
     *
     * @return string|false
     */
    public function getCpEditUrl()
    {
        $position = $this->getPosition();

        if ($position)
        {
            return UrlHelper::getCpUrl('adwizard/'.$position->handle.'/'.$this->id);
        }
    }

    /**
     * Returns the ad's position.
     *
     * @return AdWizard_PositionModel|null
     */
    public function getPosition()
    {
        if ($this->positionId)
        {
            return craft()->adWizard->getPositionById($this->positionId);
        }
    }
}
