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

/**
 * Class TrackingController
 * @since 2.0.0
 */
class TrackingController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * @access protected
     */
    protected $allowAnonymous = true;

    // Track click of ad
    public function actionClick()
    {
        $this->requirePostRequest();

        // Get ad ID
        $id = Craft::$app->getRequest()->getBodyParam('id');

        // Track click
        $success = AdWizard::$plugin->adWizard_tracking->trackClick($id);

        // If unsuccessful, return message
        if (!$success) {
            return $this->asJson('[Ad Wizard] Click tracking failed.');
        }

        // Return title of ad clicked
        $ad = AdWizard::$plugin->adWizard_ads->getAdById($id);
        return $this->asJson('[Ad Wizard] Clicked: '.$ad->title);
    }

}
