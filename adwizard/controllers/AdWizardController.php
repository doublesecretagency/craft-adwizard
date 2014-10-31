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
        $this->returnJson('[Ad Wizard] '.$response);
    }
    
    // Positions

    /**
     * Position index
     */
    public function actionPositionIndex()
    {
        $variables['positions'] = craft()->adWizard->getAllPositions();

        $this->renderTemplate('adwizard/positions', $variables);
    }

    /**
     * Edit a position.
     *
     * @param array $variables
     * @throws HttpException
     * @throws Exception
     */
    public function actionEditPosition(array $variables = array())
    {
        $variables['brandNewPosition'] = false;

        if (!empty($variables['positionId']))
        {
            if (empty($variables['position']))
            {
                $variables['position'] = craft()->adWizard->getPositionById($variables['positionId']);

                if (!$variables['position'])
                {
                    throw new HttpException(404);
                }
            }

            $variables['title'] = $variables['position']->name;
        }
        else
        {
            if (empty($variables['position']))
            {
                $variables['position'] = new AdWizard_PositionModel();
                $variables['brandNewPosition'] = true;
            }

            $variables['title'] = Craft::t('Create a new position');
        }

        $variables['crumbs'] = array(
            array('label' => Craft::t('Ads'), 'url' => UrlHelper::getUrl('adwizard')),
            array('label' => Craft::t('Positions'), 'url' => UrlHelper::getUrl('adwizard/positions')),
        );

        $this->renderTemplate('adwizard/positions/_edit', $variables);
    }

    /**
     * Saves a position
     */
    public function actionSavePosition()
    {
        $this->requirePostRequest();

        $position = new AdWizard_PositionModel();

        // Shared attributes
        $position->id     = craft()->request->getPost('positionId');
        $position->name   = craft()->request->getPost('name');
        $position->handle = craft()->request->getPost('handle');

        // Save it
        if (craft()->adWizard->savePosition($position))
        {
            craft()->userSession->setNotice(Craft::t('Position saved.'));
            $this->redirectToPostedUrl($position);
        }
        else
        {
            craft()->userSession->setError(Craft::t('Couldn’t save position.'));
        }

        // Send the position back to the template
        craft()->urlManager->setRouteVariables(array(
            'position' => $position
        ));
    }

    /**
     * Deletes an position.
     */
    public function actionDeletePosition()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $positionId = craft()->request->getRequiredPost('id');

        craft()->adWizard->deletePositionById($positionId);
        $this->returnJson(array('success' => true));
    }

    // Ads

    /**
     * Ad index
     */
    public function actionAdIndex()
    {
        $variables['positions'] = craft()->adWizard->getAllPositions();

        $this->renderTemplate('adwizard/index', $variables);
    }

    /**
     * Edit an ad.
     *
     * @param array $variables
     * @throws HttpException
     */
    public function actionEditAd(array $variables = array())
    {
        if (!empty($variables['positionHandle']))
        {
            $variables['position'] = craft()->adWizard->getPositionByHandle($variables['positionHandle']);
        }
        else if (!empty($variables['positionId']))
        {
            $variables['position'] = craft()->adWizard->getPositionById($variables['positionId']);
        }

        if (empty($variables['position']))
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
                $variables['ad']->positionId = $variables['position']->id;
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
            array('label' => $variables['position']->name, 'url' => UrlHelper::getUrl('adwizard'))
        );

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = 'adwizard/'.$variables['position']->handle.'/{id}';

        // Render the template!
        $this->renderTemplate('adwizard/_edit', $variables);
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
        $ad->positionId = craft()->request->getPost('positionId', $ad->positionId);
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
