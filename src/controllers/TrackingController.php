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
use craft\web\Controller;
use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\elements\Ad;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Class TrackingController
 * @since 2.0.0
 */
class TrackingController extends Controller
{

    /**
     * @inheritdoc
     */
    protected array|bool|int $allowAnonymous = true;

    /**
     * Track click of ad.
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionClick(): Response
    {
        $this->requirePostRequest();

        // Get ad ID
        $id = Craft::$app->getRequest()->getBodyParam('id');

        // Track click
        $success = AdWizard::$plugin->tracking->trackClick($id);

        // If unsuccessful, return message
        if (!$success) {
            return $this->asJson('[Ad Wizard] Click tracking failed.');
        }

        // Return title of ad clicked
        /** @var Ad $ad */
        $ad = AdWizard::$plugin->ads->getAdById($id);
        return $this->asJson('[Ad Wizard] Clicked: '.$ad->title);
    }

}
