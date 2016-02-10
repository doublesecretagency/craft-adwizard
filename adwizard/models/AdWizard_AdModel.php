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
			'groupId'     => AttributeType::Number,
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
	 * @inheritDoc BaseElementModel::getThumbUrl()
	 *
	 * @param int $size
	 *
	 * @return string|null
	 */
	public function getThumbUrl($size = 125)
	{
		$asset = craft()->assets->getFileById($this->assetId);
		if ($asset)
		{
			return UrlHelper::getResourceUrl('assetthumbs/'.$this->assetId.'/'.$size, array(
				craft()->resources->dateParam => $asset->dateModified->getTimestamp()
			));
		}
		else
		{
			return UrlHelper::getResourceUrl('assetthumbs//125'); // Broken image icon
		}
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
		$group = $this->getGroup();

		if ($group)
		{
			return UrlHelper::getCpUrl('adwizard/'.$group->handle.'/'.$this->id);
		}
	}

	/**
	 * Returns the ad's group.
	 *
	 * @return AdWizard_GroupModel|null
	 */
	public function getGroup()
	{
		if ($this->groupId)
		{
			return craft()->adWizard->getGroupById($this->groupId);
		}
	}
}
