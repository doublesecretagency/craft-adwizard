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

use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\elements\Asset;
use craft\errors\ElementNotFoundException;
use craft\errors\InvalidElementException;
use craft\errors\MissingComponentException;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\web\assets\AdGroupSwitcherAssets;
use doublesecretagency\adwizard\web\assets\AdminAssets;
use Exception;
use Throwable;
use yii\base\Exception as YiiException;
use yii\base\InvalidConfigException;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Class AdsController
 * @since 2.0.0
 */
class AdsController extends Controller
{

    /**
     * Called before displaying the ads page.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $this->requireLogin();

        $groups = AdWizard::$plugin->groups->getAllGroups();

        // Breadcrumbs
        $crumbs = [
            [
                'label' => Craft::t('ad-wizard', 'Ad Wizard'),
                'url'   => UrlHelper::url('ad-wizard')
            ],
            [
                'label' => Craft::t('ad-wizard', 'Ads'),
                'url'   => UrlHelper::url('ad-wizard/ads')
            ]
        ];

        return $this->renderTemplate('ad-wizard/ads', [
            'elementType' => Ad::class,
            'fullPageForm' => true,
            'groups' => $groups,
            'crumbs' => $crumbs,
        ]);
    }

    /**
     * Displays the ad edit page.
     *
     * @param string|null $groupHandle The ad group’s handle.
     * @param int|null $adId The ad’s ID, if editing an existing ad.
     * @param string|null $siteHandle The site handle, if specified.
     * @param Ad|null $ad The ad being edited, if there were any validation errors.
     * @return Response
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws YiiException
     */
    public function actionEditAd(?string $groupHandle = null, ?int $adId = null, ?string $siteHandle = null, ?Ad $ad = null): Response
    {
        $variables = [
            'groupHandle' => $groupHandle,
            'adId' => $adId,
            'ad' => $ad,
            'fullPageForm' => true,
            'groupData' => []
        ];

        $allGroups = AdWizard::$plugin->groups->getAllGroups();

        foreach ($allGroups as $group) {
            $variables['groupData'][$group->id] = [
                'name' => $group->name,
                'layoutId' => $group->fieldLayoutId,
                'redirectHash' => Craft::$app->getSecurity()->hashData("ad-wizard/ads/{$group->handle}")
            ];
        }

        if (!empty($variables['groupHandle'])) {
            $variables['group'] = AdWizard::$plugin->groups->getGroupByHandle($variables['groupHandle']);
        } else if (!empty($variables['groupId'])) {
            $variables['group'] = AdWizard::$plugin->groups->getGroupById($variables['groupId']);
        } else if (!empty($allGroups)) {
            $variables['group'] = $allGroups[0];
        }

        if (empty($variables['group'])) {
            throw new NotFoundHttpException('Ad group not found');
        }

        // Ensure group handle is valid
        $groupHandle = $variables['group']->handle;

        // Multiple ad groups
        // ---------------------------------------------------------------------

        // If more than one ad group
        if (count($allGroups) > 1) {

            // Load ad group switcher JS
            $view = $this->getView();
            $view->registerJs('new Craft.AdGroupSwitcher();');
            $view->registerAssetBundle(AdGroupSwitcherAssets::class);

        }

        // Get the ad
        // ---------------------------------------------------------------------

        if (empty($variables['ad'])) {
            if (!empty($variables['adId'])) {
                $variables['ad'] = AdWizard::$plugin->ads->getAdById($variables['adId']);

                if (!$variables['ad']) {
                    throw new NotFoundHttpException('Ad not found');
                }
            } else {
                $variables['ad'] = new Ad();
                $variables['ad']->groupId = $variables['group']->id;
                $variables['ad']->enabled = true;
            }
        }

        // Prep the form tabs & content
        $fieldLayout = $variables['group']->getFieldLayout();
        if ($fieldLayout) {
            $form = $fieldLayout->createForm($variables['ad']);
            $variables['fieldsHtml'] = $form->render();
        } else {
            $variables['fieldsHtml'] = null;
        }

        // ---------------------------------------------------------------------

        // Whether this is a new ad
        $newAd = ($variables['ad']->id === null);

        // Whether any asset sources exist
        $sources = Craft::$app->getAssets()->findFolders();
        $variables['assetsSourceExists'] = count($sources);

        // Set asset ID
        $variables['assetId'] = $variables['ad']->assetId;

        // Set asset elements
        if ($variables['assetId']) {
            if (is_array($variables['assetId'])) {
                $variables['assetId'] = $variables['assetId'][0];
            }
            $asset = Craft::$app->getElements()->getElementById($variables['assetId'], Asset::class);
            $variables['elements'] = [$asset];
        } else {
            $variables['elements'] = [];
        }

        // Set element type
        $variables['elementType'] = Asset::class;

        // Other variables
        // ---------------------------------------------------------------------

        // Page title
        if ($newAd) {
            $variables['title'] = Craft::t('ad-wizard', 'Create a new ad');
        } else {
            $variables['title'] = $variables['ad']->title;
        }

        // Breadcrumbs
        $variables['crumbs'] = [
            [
                'label' => Craft::t('ad-wizard', 'Ad Wizard'),
                'url'   => UrlHelper::url('ad-wizard')
            ],
            [
                'label' => Craft::t('ad-wizard', 'Ads'),
                'url'   => UrlHelper::url('ad-wizard/ads')
            ]
        ];

        if ($newAd) {
            $variables['crumbs'][] = [
                'label' => Craft::t('ad-wizard', 'Create New Ad'),
                'url'   => UrlHelper::url('ad-wizard/ads/new')
            ];
        } else {
            $variables['crumbs'][] = [
                'label' => Craft::t('site', $variables['ad']->title),
                'url'   => $variables['ad']->getCpEditUrl()
            ];
        }

        // Set the "Continue Editing" URL
        $variables['redirectUrl'] = [
            'continueEditing' => "ad-wizard/ads/{$groupHandle}/{id}",
            'addAnother'      => "ad-wizard/ads/{$groupHandle}/new",
            'index'           => 'ad-wizard/ads',
        ];

        // Set whether ad is new
        $variables['isNewAd'] = ($variables['ad']->id ? false : true);

        // Register assets
        Craft::$app->getView()->registerAssetBundle(AdminAssets::class);

        // Render the template!
        return $this->renderTemplate('ad-wizard/ads/_edit', $variables);
    }

    /**
     * Saves an ad.
     *
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws YiiException
     */
    public function actionSaveAd(): ?Response
    {
        $this->requirePostRequest();

        $ad = $this->_getAdModel();
        $request = Craft::$app->getRequest();

        // Are we duplicating the ad?
        if ($request->getBodyParam('duplicate')) {
            // Swap $ad with the duplicate
            try {
                $ad = Craft::$app->getElements()->duplicateElement($ad);
            } catch (InvalidElementException $e) {
                /** @var Ad $clone */
                $clone = $e->element;

                if ($request->getAcceptsJson()) {
                    return $this->asJson([
                        'success' => false,
                        'errors' => $clone->getErrors(),
                    ]);
                }

                Craft::$app->getSession()->setError(Craft::t('ad-wizard', 'Couldn’t duplicate ad.'));

                // Send the original ad back to the template, with any validation errors on the clone
                $ad->addErrors($clone->getErrors());
                Craft::$app->getUrlManager()->setRouteParams([
                    'ad' => $ad
                ]);

                return null;
            } catch (Throwable $e) {
                throw new ServerErrorHttpException(Craft::t('ad-wizard', 'An error occurred when duplicating the ad.'), 0, $e);
            }
        }

        // Populate the ad with post data
        $this->_populateAdModel($ad);
        $ad->setFieldValuesFromRequest('fields');

        // Validate
        if (!$ad->validate()) {
            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $ad->getErrors(),
                ]);
            }

            Craft::$app->getSession()->setError(Craft::t('ad-wizard', 'Couldn’t save ad.'));

            // Send the ad back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'ad' => $ad
            ]);

            return null;
        }

        // Save the ad
        if ($ad->enabled && $ad->enabledForSite) {
            $ad->setScenario(Element::SCENARIO_LIVE);
        }

        if (!Craft::$app->getElements()->saveElement($ad)) {
            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $ad->getErrors(),
                ]);
            }

            Craft::$app->getSession()->setError(Craft::t('ad-wizard', 'Couldn’t save ad.'));

            // Send the ad back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'ad' => $ad
            ]);

            return null;
        }

        // Get field layout
        $fieldLayoutId = (new Query())
            ->select(['fieldLayoutId'])
            ->from(['{{%adwizard_groups}}'])
            ->where(['id' => $ad->groupId])
            ->scalar();

        // Update all ads in group with new layout
        AdWizard::$plugin->ads->updateAdsLayout($fieldLayoutId, $ad->groupId);

        if ($request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'id' => $ad->id,
                'title' => $ad->title,
                'url' => $ad->getUrl(),
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('ad-wizard', 'Ad saved.'));

        return $this->redirectToPostedUrl($ad);
    }

    /**
     * Deletes an ad.
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws MissingComponentException
     */
    public function actionDeleteAd(): Response
    {
        $this->requirePostRequest();
        $this->requireLogin();

        $adId = Craft::$app->getRequest()->getRequiredBodyParam('adId');

        Craft::$app->getElements()->deleteElementById($adId, Ad::class);

        Craft::$app->getSession()->setNotice(Craft::t('ad-wizard', 'Ad deleted.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * Switches between two ad groups.
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionSwitchAdGroup(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $ad = $this->_getAdModel();
        $this->_populateAdModel($ad);

        $variables = [
            'ad' => $ad
        ];

        // Prep the form tabs & content
        $fieldLayout = $ad->getGroup()->getFieldLayout();
        if ($fieldLayout) {
            $form = $fieldLayout->createForm($variables['ad']);
            $fieldsHtml = $form->render();
        } else {
            $fieldsHtml = null;
        }

        $view = $this->getView();
        $headHtml = $view->getHeadHtml();
        $bodyHtml = $view->getBodyHtml();

        return $this->asJson(compact(
            'fieldsHtml',
            'headHtml',
            'bodyHtml'
        ));
    }

    /**
     * Fetches or creates an Ad.
     *
     * @return Ad
     * @throws BadRequestHttpException if the requested ad group doesn't exist
     * @throws NotFoundHttpException if the requested ad cannot be found
     */
    private function _getAdModel(): Ad
    {
        $request = Craft::$app->getRequest();

        $adId = $request->getBodyParam('adId');

        if ($adId) {
            $ad = AdWizard::$plugin->ads->getAdById($adId);

            if (!$ad) {
                throw new NotFoundHttpException('Ad not found');
            }
        } else {
            $groupId = $request->getRequiredBodyParam('groupId');
            if (($group = AdWizard::$plugin->groups->getGroupById($groupId)) === null) {
                throw new BadRequestHttpException('Invalid ad group ID: '.$groupId);
            }

            $ad = new Ad();
            $ad->groupId = $group->id;
        }

        return $ad;
    }

    /**
     * Populates an Ad with post data.
     *
     * @param Ad $ad
     * @throws Exception
     */
    private function _populateAdModel(Ad $ad): void
    {
        $request = Craft::$app->getRequest();

        // Set the ad attributes, defaulting to the existing values for whatever is missing from the post data
        $ad->groupId = $request->getBodyParam('groupId', $ad->groupId);
        $ad->url     = $request->getBodyParam('url', $ad->url);

        // Get asset, if specified
        $assets = $request->getBodyParam('adGraphic', [$ad->assetId]);
        $ad->assetId = (!empty($assets) ? $assets[0] : null);

        if (($startDate = Craft::$app->getRequest()->getBodyParam('startDate')) !== null) {
            $ad->startDate = DateTimeHelper::toDateTime($startDate) ?: null;
        }
        if (($endDate = Craft::$app->getRequest()->getBodyParam('endDate')) !== null) {
            $ad->endDate = DateTimeHelper::toDateTime($endDate) ?: null;
        }

        $ad->maxViews = $request->getBodyParam('maxViews', $ad->maxViews);
        $ad->enabled = (bool) $request->getBodyParam('enabled', $ad->enabled);

        $ad->title = $request->getBodyParam('title', $ad->title);

        if (!$ad->groupId) {
            // Default to the first available ad group
            $ad->groupId = AdWizard::$plugin->groups->getAllGroups()[0]->id;
        }

        // Prevent the last ad group's field layout from being used
        $ad->fieldLayoutId = null;

        $group = AdWizard::$plugin->groups->getGroupById($ad->groupId);

        // Update field layout
        if ($group) {
            $ad->fieldLayoutId = $group->fieldLayoutId;
        }
    }

}
