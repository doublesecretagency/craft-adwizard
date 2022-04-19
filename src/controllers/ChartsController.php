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
use yii\base\Exception;
use yii\web\Response;

/**
 * Class ChartsController
 * @since 2.0.0
 */
class ChartsController extends Controller
{

    /**
     * Returns the data needed to display an Ad Timeline chart.
     *
     * @return Response
     * @throws Exception
     */
    public function actionGetAdTimelineData(): Response
    {
        $adId = Craft::$app->getRequest()->getRequiredBodyParam('adId');

        // Get the chart data
        $data = AdWizard::$plugin->widgets->adTimelineData($adId);

        // If error message was returned
        if (is_string($data)) {
            return $this->asJson([
                'error' => $data
            ]);
        }

        // Return data
        return $this->asJson([
            'data' => $data,
        ]);
    }

    /**
     * Returns the data needed to display a Group Totals chart.
     *
     * @return Response
     * @throws Exception
     */
    public function actionGetGroupTotalsData(): Response
    {
        $groupId = Craft::$app->getRequest()->getRequiredBodyParam('groupId');

        // Get the chart data
        $data = AdWizard::$plugin->widgets->groupTotalsData($groupId);

        // If error message was returned
        if (is_string($data)) {
            return $this->asJson([
                'error' => $data
            ]);
        }

        // Return data
        return $this->asJson([
            'data' => $data
        ]);
    }

}
