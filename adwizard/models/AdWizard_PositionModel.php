<?php
namespace Craft;

/**
 * AdWizard - Position model
 */
class AdWizard_PositionModel extends BaseModel
{
    /**
     * Use the translated position name as the string representation.
     *
     * @return string
     */
    function __toString()
    {
        return Craft::t($this->name);
    }

    /**
     * @access protected
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'id'            => AttributeType::Number,
            'name'          => AttributeType::String,
            'handle'        => AttributeType::String,
        );
    }
}
