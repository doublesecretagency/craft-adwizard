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

namespace doublesecretagency\adwizard\controllers;

use yii\base\Response;
use yii\web\HttpException;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;

use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\models\AdGroup;

/**
 * Class AdGroupsController
 * @since 2.0.0
 */
class AdGroupsController extends Controller
{

    /**
     * Called before displaying the ad groups page.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $this->requireLogin();

        $groups = AdWizard::$plugin->adWizard_groups->getAllGroups();

        return $this->renderTemplate('ad-wizard/groups', [
            'crumbs' => $this->_groupsCrumbs(),
            'selectedSubnavItem' => 'groups',
            'fullPageForm' => true,
            'groups' => $groups,
        ]);
    }

    /**
     * Edit an ad group.
     *
     * @param int|null $groupId The group’s ID, if any.
     * @param AdGroup|null $group The group being edited, if there were any validation errors.
     * @return Response
     * @throws HttpException if the requested group cannot be found
     */
    public function actionEditAdGroup(int $groupId = null, AdGroup $group = null): Response
    {
        $this->requireLogin();

        if ($groupId !== null) {
            if ($group === null) {
                $group = AdWizard::$plugin->adWizard_groups->getGroupById($groupId);

                if (!$group) {
                    throw new HttpException('Ad group not found');
                }
            }

            $title = $group->name;
        } else {
            if ($group === null) {
                $group = new AdGroup();
            }

            $title = Craft::t('ad-wizard', 'Create a new group');
        }

        // Breadcrumbs
        $crumbs = $this->_groupsCrumbs();

        // Append final crumb
        if ($group->id) {
            $crumbs[] = [
                'label' => Craft::t('site', $group->name),
                'url'   => UrlHelper::cpUrl('ad-wizard/groups/'.$group->id)
            ];
        } else {
            $crumbs[] = [
                'label' => Craft::t('ad-wizard', 'Create New Group'),
                'url'   => UrlHelper::cpUrl('ad-wizard/groups/new')
            ];
        }

        return $this->renderTemplate('ad-wizard/groups/_edit', [
            'crumbs' => $crumbs,
            'selectedSubnavItem' => 'groups',
            'fullPageForm' => true,
            'groupId' => $groupId,
            'group' => $group,
            'title' => $title,
        ]);
    }

    /**
     * Save an ad group.
     *
     * @return Response|null
     */
    public function actionSaveGroup()
    {
        $this->requirePostRequest();
        $this->requireLogin();

        $group = new AdGroup();

        // Get request
        $request = Craft::$app->getRequest();

        // Get POST values
        $group->id     = $request->getBodyParam('groupId');
        $group->name   = $request->getBodyParam('name');
        $group->handle = $request->getBodyParam('handle');

        // Save it
        if (!AdWizard::$plugin->adWizard_groups->saveGroup($group)) {
            Craft::$app->getSession()->setError(Craft::t('ad-wizard', 'Couldn’t save the group.'));

            // Send the ad group back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'group' => $group
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('ad-wizard', 'Ad group saved.'));

        return $this->redirectToPostedUrl($group);
    }

    /**
     * Deletes an ad group.
     *
     * @return Response
     */
    public function actionDeleteAdGroup(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requireLogin();

        $groupId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        AdWizard::$plugin->adWizard_groups->deleteGroupById($groupId);

        return $this->asJson(['success' => true]);
    }

    // Private Methods
    // =========================================================================

    /**
     * Breadcrumbs for ad group pages.
     *
     * @return array
     */
    private function _groupsCrumbs(): array
    {
        return [
            [
                'label' => Craft::t('ad-wizard', 'Ad Wizard'),
                'url'   => UrlHelper::cpUrl('ad-wizard')
            ],
            [
                'label' => Craft::t('ad-wizard', 'Groups'),
                'url'   => UrlHelper::cpUrl('ad-wizard/groups')
            ]
        ];
    }

}
