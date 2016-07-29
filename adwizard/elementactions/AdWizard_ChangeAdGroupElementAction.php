<?php

namespace Craft;

class AdWizard_ChangeAdGroupElementAction extends BaseElementAction
{
	public function getName()
	{
		return Craft::t('Change Ad Group');
	}

	public function getTriggerHtml()
	{
		// Render the trigger menu template with all the available groups
		$groups = craft()->adWizard->getAllGroups();

		// Return template
		return craft()->templates->render('adwizard/_elementactions/changeAdGroup', array(
			'groups' => $groups
		));
	}

	public function performAction(ElementCriteriaModel $criteria)
	{
		// Get the selected group
		$groupId = $this->getParams()->groupId;
		$group = craft()->adWizard->getGroupById($groupId);

		// Make sure it's a valid group
		if (!$group) {
			$this->setMessage(Craft::t('The selected group could not be found.'));
			return false;
		}

		// Set group of the selected ads
		foreach ($criteria->find() as $ad) {
			craft()->adWizard->moveAdToGroup($ad, $groupId);
		}

		// Success!
		$this->setMessage(Craft::t('Moved to').' "'.$group->name.'"');
		return true;
	}

	protected function defineParams()
	{
		return array(
			'groupId' => array(AttributeType::Number, 'required' => true),
		);
	}
}