<?php
/**
 * Ad Wizard plugin for Craft CMS
 *
 * Easily manage custom advertisements on your website.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2014 Double Secret Agency
 */

namespace doublesecretagency\adwizard\elements\actions;

use Craft;
use craft\base\Element;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use doublesecretagency\adwizard\AdWizard;

/**
 * Class ChangeAdGroup
 * @since 2.0.0
 */
class ChangeAdGroup extends ElementAction
{

    /**
     * @var int|null The group ads should be set to.
     */
    public ?int $groupId = null;

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('ad-wizard', 'Change Ad Group');
    }

    /**
     * @inheritdoc
     */
    public function getTriggerHtml(): ?string
    {
        // Render the trigger menu template with all the available groups
        $groups = AdWizard::$plugin->groups->getAllGroups();

        // Return template
        return Craft::$app->getView()->renderTemplate('ad-wizard/elementactions/ChangeAdGroup', [
            'groups' => $groups
        ]);
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        // Get the selected group
        $group = AdWizard::$plugin->groups->getGroupById($this->groupId);

        // Make sure it's a valid group
        if (!$group) {
            $this->setMessage(Craft::t('ad-wizard', 'The selected group could not be found.'));
            return false;
        }

        /** @var Element[] $elements */
        $adIds = $query->ids();

        // Set group of the selected ads
        AdWizard::$plugin->ads->updateAdsGroup($adIds, $this->groupId);

        // Success!
        $this->setMessage(Craft::t('ad-wizard', 'Moved to "{groupName}".', [
            'groupName' => $group->name
        ]));

        return true;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['groupId'], 'required'];
        $rules[] = [['groupId'], 'number', 'integerOnly' => true];

        return $rules;
    }

}
