<?php
namespace Craft;

/**
 * AdWizard - Group model
 */
class AdWizard_GroupModel extends BaseModel
{
	/**
	 * Use the translated group name as the string representation.
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
			'id'     => AttributeType::Number,
			'name'   => AttributeType::String,
			'handle' => AttributeType::String,
		);
	}
}
