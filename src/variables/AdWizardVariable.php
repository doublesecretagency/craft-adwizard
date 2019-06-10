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

namespace doublesecretagency\adwizard\variables;

use Craft;
use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\elements\db\AdQuery;
use doublesecretagency\adwizard\models\AdGroup;
use Twig\Markup;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

/**
 * Class AdWizardVariable
 * @since 2.0.0
 */
class AdWizardVariable
{

    /**
     * Returns a new AdQuery instance.
     *
     * @param array $criteria
     * @return AdQuery
     */
    public function ads(array $criteria = []): AdQuery
    {
        $query = Ad::find();
        Craft::configure($query, $criteria);
        return $query;
    }

    // ========================================================================= //

    /**
     * Display specified ad.
     *
     * @param $id
     * @param null $transform
     * @param bool $retina
     * @return bool|Markup
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function displayAd($id, $transform = null, $retina = false)
    {
        return AdWizard::$plugin->ads->renderAd($id, $transform, $retina);
    }

    /**
     * Display random ad from specified ad group.
     *
     * @param $group
     * @param null $transform
     * @param bool $retina
     * @return bool|Markup
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function randomizeAdGroup($group, $transform = null, $retina = false)
    {
        return AdWizard::$plugin->ads->renderRandomAdFromGroup($group, $transform, $retina);
    }

    // ========================================================================= //

    /**
     * Get all groups.
     *
     * @return array
     */
    public function getAllGroups(): array
    {
        return AdWizard::$plugin->groups->getAllGroups();
    }

    /**
     * Get group by id.
     *
     * @param $groupId
     * @return AdGroup|null
     */
    public function getGroupById($groupId)
    {
        return AdWizard::$plugin->groups->getGroupById($groupId);
    }

    // ========================================================================= //

    /**
     * Get month total of views.
     *
     * @param $id
     * @param $year
     * @param $month
     * @return int
     */
    public function monthTotalViews($id, $year, $month): int
    {
        return AdWizard::$plugin->tracking->monthTotalViews($id, $year, $month);
    }

    /**
     * Get month total of clicks.
     *
     * @param $id
     * @param $year
     * @param $month
     * @return int
     */
    public function monthTotalClicks($id, $year, $month): int
    {
        return AdWizard::$plugin->tracking->monthTotalClicks($id, $year, $month);
    }

    // ========================================================================= //

    /**
     * Link to full documentation.
     *
     * @return string
     */
    public function docsUrl(): string
    {
        return AdWizard::DOCS_URL;
    }

}
