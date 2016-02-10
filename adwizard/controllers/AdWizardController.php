<?php
namespace Craft;

/**
 * Ad Wizard controller
 */
class AdWizardController extends BaseController
{

	protected $allowAnonymous = array('actionClick');

	// Track click of ad
	public function actionClick()
	{
		$this->requireAjaxRequest();
		$id = craft()->request->getPost('id');
		$success = craft()->adWizard->trackClick($id);
		if ($success) {
			$ad = craft()->adWizard->getAdById($id);
			$response = 'Clicked: '.$ad->title;
		} else {
			$response = 'Click tracking failed.';
		}
		$this->returnJson(craft()->adWizard->errorPrefix.$response);
	}

	// Groups

	/**
	 * Group index
	 */
	public function actionGroupIndex()
	{
		$variables['groups'] = craft()->adWizard->getAllGroups();

		$this->renderTemplate('adwizard/groups', $variables);
	}

	/**
	 * Edit a group.
	 *
	 * @param array $variables
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditGroup(array $variables = array())
	{
		$variables['brandNewGroup'] = false;

		if (!empty($variables['groupId']))
		{
			if (empty($variables['group']))
			{
				$variables['group'] = craft()->adWizard->getGroupById($variables['groupId']);

				if (!$variables['group'])
				{
					throw new HttpException(404);
				}
			}

			$variables['title'] = $variables['group']->name;
		}
		else
		{
			if (empty($variables['group']))
			{
				$variables['group'] = new AdWizard_GroupModel();
				$variables['brandNewGroup'] = true;
			}

			$variables['title'] = Craft::t('Create a new group');
		}

		$variables['crumbs'] = array(
			array('label' => Craft::t('Ads'), 'url' => UrlHelper::getUrl('adwizard')),
			array('label' => Craft::t('Groups'), 'url' => UrlHelper::getUrl('adwizard/groups')),
		);

		$this->renderTemplate('adwizard/groups/_edit', $variables);
	}

	/**
	 * Saves a group
	 */
	public function actionSaveGroup()
	{
		$this->requirePostRequest();

		$group = new AdWizard_GroupModel();

		// Shared attributes
		$group->id     = craft()->request->getPost('groupId');
		$group->name   = craft()->request->getPost('name');
		$group->handle = craft()->request->getPost('handle');

		// Save it
		if (craft()->adWizard->saveGroup($group))
		{
			craft()->userSession->setNotice(Craft::t('Group saved.'));
			$this->redirectToPostedUrl($group);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save group.'));
		}

		// Send the group back to the template
		craft()->urlManager->setRouteVariables(array(
			'group' => $group
		));
	}

	/**
	 * Deletes an group.
	 */
	public function actionDeleteGroup()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$groupId = craft()->request->getRequiredPost('id');

		craft()->adWizard->deleteGroupById($groupId);
		$this->returnJson(array('success' => true));
	}

	// Ads

	/**
	 * Ad index
	 */
	public function actionAdIndex()
	{
		$variables['groups'] = craft()->adWizard->getAllGroups();

		$this->renderTemplate('adwizard/ads', $variables);
	}

	/**
	 * Edit an ad.
	 *
	 * @param array $variables
	 * @throws HttpException
	 */
	public function actionEditAd(array $variables = array())
	{
		if (!empty($variables['groupHandle']))
		{
			$variables['group'] = craft()->adWizard->getGroupByHandle($variables['groupHandle']);
		}
		else if (!empty($variables['groupId']))
		{
			$variables['group'] = craft()->adWizard->getGroupById($variables['groupId']);
		}

		if (empty($variables['group']))
		{
			throw new HttpException(404);
		}

		// Now let's set up the actual ad
		if (empty($variables['ad']))
		{
			if (!empty($variables['adId']))
			{
				$variables['ad'] = craft()->adWizard->getAdById($variables['adId']);

				if (!$variables['ad'])
				{
					throw new HttpException(404);
				}
			}
			else
			{
				$variables['ad'] = new AdWizard_AdModel();
				$variables['ad']->groupId = $variables['group']->id;
			}
		}

		// Whether any assets sources exist
		$sources = craft()->assets->findFolders();
		$variables['assetsSourceExists'] = count($sources);

		// URL to create a new assets source
		$variables['newAssetsSourceUrl'] = UrlHelper::getUrl('settings/assets/sources/new');

		// Set asset ID
		$variables['assetId'] = $variables['ad']->assetId;

		// Set asset elements
		if ($variables['assetId']) {
			if (is_array($variables['assetId'])) {
				$variables['assetId'] = $variables['assetId'][0];
			}
			$asset = craft()->elements->getElementById($variables['assetId']);
			$variables['elements'] = array($asset);
		} else {
			$variables['elements'] = array();
		}

		// Set element type
		$variables['elementType'] = craft()->elements->getElementType(ElementType::Asset);

		// Tabs
		$variables['tabs'] = array();

		if (!$variables['ad']->id)
		{
			$variables['title'] = Craft::t('Create a new ad');
		}
		else
		{
			$variables['title'] = $variables['ad']->title;
		}

		// Breadcrumbs
		$variables['crumbs'] = array(
			array('label' => Craft::t('Ads'), 'url' => UrlHelper::getUrl('adwizard')),
			array('label' => $variables['group']->name, 'url' => UrlHelper::getUrl('adwizard'))
		);

		// Set the "Continue Editing" URL
		$variables['continueEditingUrl'] = 'adwizard/'.$variables['group']->handle.'/{id}';

		// Render the template!
		$this->renderTemplate('adwizard/ads/_edit', $variables);
	}

	/**
	 * Saves an ad.
	 */
	public function actionSaveAd()
	{
		$this->requirePostRequest();

		$adId = craft()->request->getPost('adId');

		if ($adId)
		{
			$ad = craft()->adWizard->getAdById($adId);

			if (!$ad)
			{
				throw new Exception(Craft::t('No ad exists with the ID “{id}”', array('id' => $adId)));
			}
		}
		else
		{
			$ad = new AdWizard_AdModel();
		}

		// Set the ad attributes, defaulting to the existing values for whatever is missing from the post data
		$ad->groupId = craft()->request->getPost('groupId', $ad->groupId);
		$ad->assetId    = craft()->request->getPost('assetId', $ad->assetId);
		$ad->url        = craft()->request->getPost('url', $ad->url);
		$ad->details    = craft()->request->getPost('details', $ad->details);
		$ad->startDate  = (($startDate = craft()->request->getPost('startDate')) ? DateTime::createFromString($startDate, craft()->timezone) : null);
		$ad->endDate    = (($endDate   = craft()->request->getPost('endDate'))   ? DateTime::createFromString($endDate,   craft()->timezone) : null);
		$ad->maxViews   = craft()->request->getPost('maxViews', $ad->maxViews);
		$ad->enabled    = (bool) craft()->request->getPost('enabled', $ad->enabled);

		$ad->getContent()->title = craft()->request->getPost('title', $ad->title);

		if (craft()->adWizard->saveAd($ad))
		{
			craft()->userSession->setNotice(Craft::t('Ad saved.'));
			$this->redirectToPostedUrl($ad);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save ad.'));

			// Send the ad back to the template
			craft()->urlManager->setRouteVariables(array(
				'ad' => $ad
			));
		}
	}

	/**
	 * Deletes an ad.
	 */
	public function actionDeleteAd()
	{
		$this->requirePostRequest();

		$adId = craft()->request->getRequiredPost('adId');

		if (craft()->elements->deleteElementById($adId))
		{
			craft()->userSession->setNotice(Craft::t('Ad deleted.'));
			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t delete ad.'));
		}
	}
}
