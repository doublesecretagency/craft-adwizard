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

    // Display specified ad
    public function displayAd($id, $transform = null, $retina = false)
    {
        return AdWizard::$plugin->adWizard_ads->renderAd($id, $transform, $retina);
    }

    // Display random ad from specified ad group
    public function randomizeAdGroup($group, $transform = null, $retina = false)
    {
        return AdWizard::$plugin->adWizard_ads->renderRandomAdFromGroup($group, $transform, $retina);
    }

    // ========================================================================= //

    // Get all groups
    public function getAllGroups()
    {
        return AdWizard::$plugin->adWizard_groups->getAllGroups();
    }

    // Get group by id
    public function getGroupById($groupId)
    {
        return AdWizard::$plugin->adWizard_groups->getGroupById($groupId);
    }

    // ========================================================================= //

    // Get month total of views
    public function monthTotalViews($id, $year, $month)
    {
        return AdWizard::$plugin->adWizard_tracking->monthTotalViews($id, $year, $month);
    }

    // Get month total of clicks
    public function monthTotalClicks($id, $year, $month)
    {
        return AdWizard::$plugin->adWizard_tracking->monthTotalClicks($id, $year, $month);
    }

    // ========================================================================= //

    // Link to full documentation
    public function docsUrl()
    {
        return AdWizard::$plugin->documentationUrl;
    }

}
