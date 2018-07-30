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

namespace doublesecretagency\adwizard\services;

use Craft;
use craft\base\Component;

use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\records\Click;
use doublesecretagency\adwizard\records\View;

/**
 * Class Widgets
 * @since 2.0.0
 */
class Widgets extends Component
{

    //
    public function adTimelineData($adId = null)
    {
        // If no ID, bail
        if (!$adId) {
            return 'No ad specified.';
        }

        // Get ad
        $ad = AdWizard::$plugin->adWizard_ads->getAdById($adId);

        // If no ad, bail
        if (!$ad) {
            return 'Ad number "'.$adId.'" does not exist.';
        }

        // Initialize column data
        $columns = [
            'Day' => [],
            'Views' => [],
            'Clicks' => []
        ];

        // Get views & clicks
        $views  = $this->_getTrackingHistory(View::class, $ad);
        $clicks = $this->_getTrackingHistory(Click::class, $ad);

        // Loop through days of the month
        $daysinMonth = date('t');
        for ($i = 1; $i <= $daysinMonth; $i++) {
            $day = 'day'.$i;
            $paddedDay = str_pad($i, 2, 0, STR_PAD_LEFT);
            $columns['Day'][] = date('Y-m-').$paddedDay;
            if (date('j') < $i) {
                $columns['Views'][]  = null;
                $columns['Clicks'][] = null;
            } else {
                $columns['Views'][]  = (int) $views->$day;
                $columns['Clicks'][] = (int) $clicks->$day;
            }
        }

        // Return data set
        return [
            array_merge(['Day'], $columns['Day']),
            array_merge(['Views'], $columns['Views']),
            array_merge(['Clicks'], $columns['Clicks'])
        ];

    }

    //
    public function groupTotalsData($groupId = null)
    {
        // If no group ID, bail
        if (!$groupId) {
            return 'No group specified.';
        }

        // Get group
        $group = AdWizard::$plugin->adWizard_groups->getGroupById($groupId);

        // If no group, bail
        if (!$group) {
            return 'Specified group does not exist.';
        }

        // Get all ads in this group
        $ads = Ad::findAll([
            'groupId' => $groupId,
            //'startDate' => '',
            //'endDate'   => '',
            //'maxViews'  => '',
        ]);

        // If no ads, bail
        if (empty($ads)) {
            return 'No ads exist in that group.';
        }

        // Initialize column data
        $columns = [
            'Ad' => [],
            'Views' => [],
            'Clicks' => []
        ];

        // Loop through ads
        foreach ($ads as $ad) {
            if ($ad->enabled) {
                $columns['Ad'][]     = $ad->title;
                $columns['Views'][]  = $this->_getTrackingTotal(View::class, $ad);
                $columns['Clicks'][] = $this->_getTrackingTotal(Click::class, $ad);
            }
        }

        // Return data set
        return [
            $columns['Ad'],
            array_merge(['Views'], $columns['Views']),
            array_merge(['Clicks'], $columns['Clicks'])
        ];

    }

    // ========================================================================= //

    // Get tracking total for views/clicks
    private function _getTrackingTotal($record, $ad)
    {
        $tracking = $this->_getTrackingHistory($record, $ad);
        // Count tracking
        $total = 0;
        if ($tracking) {
            for ($i = 1; $i <= 31; $i++) {
                $day = 'day'.$i;
                $total += $tracking->$day;
            }
        }
        return $total;
    }

    // Get tracking history for views/clicks
    private function _getTrackingHistory($record, $ad)
    {
        $year  = date('Y');
        $month = date('n');

        $results = $record::findOne([
            'adId'  => $ad->id,
            'year'  => $year,
            'month' => $month,
        ]);

        // Get existing
        if (!$results) {
            $results = new $record;
        }
        return $results;
    }

}
