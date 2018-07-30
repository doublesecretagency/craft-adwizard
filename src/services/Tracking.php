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

use doublesecretagency\adwizard\records\Ad as AdRecord;
use doublesecretagency\adwizard\records\View;
use doublesecretagency\adwizard\records\Click;

/**
 * Class Tracking
 * @since 2.0.0
 */
class Tracking extends Component
{

    // Track view
    public function trackView($id)
    {
        $this->_incrementTotal($id, 'totalViews');
        return $this->_incrementDay($id, 'View');
    }

    // Track click
    public function trackClick($id)
    {
        $this->_incrementTotal($id, 'totalClicks');
        return $this->_incrementDay($id, 'Click');
    }

    // Increment total overall value
    private function _incrementTotal($id, $field)
    {
        // Get ad record
        $ad = AdRecord::findOne($id);
        // If no matching record, bail
        if (!$ad) {
            return false;
        }
        // Increment counter
        return $ad->updateCounters([$field => 1]);
    }

    // ========================================================================= //

    // Stats

    // Total views in specified month
    public function monthTotalViews($id, $year, $month)
    {
        return $this->_getMonthTotal($id, 'View', $year, $month);
    }

    // Total clicks in specified month
    public function monthTotalClicks($id, $year, $month)
    {
        return $this->_getMonthTotal($id, 'Click', $year, $month);
    }

    // ========================================================================= //

    // Increment total daily value
    private function _incrementDay($id, $recordName)
    {
        // Date info
        $year   = date('Y');
        $month  = date('n');
        $day    = 'day'.date('j');
        // Get tracking info
        $record = $this->_recordNamespace($recordName);
        $tracking = $this->_getTracking($record, $id, $year, $month);
        // If no tracking exists
        if (!$tracking) {
            // Create a new tracking record
            $trackingRecord = new $record;
            $trackingRecord->adId  = $id;
            $trackingRecord->year  = $year;
            $trackingRecord->month = $month;
            $trackingRecord->$day  = 1;
            $trackingRecord->total = 1;
            return $trackingRecord->save();
        }
        // Update existing tracking record
        return $tracking->updateCounters([
            $day    => 1,
            'total' => 1,
        ]);
    }

    // ========================================================================= //

    /**
     * Get total number of views/clicks per month
     *
     * @return int
     */
    public function _getMonthTotal($id, $recordName, $year, $month)
    {
        // Get tracking info
        $record = $this->_recordNamespace($recordName);
        $tracking = $this->_getTracking($record, $id, $year, $month);
        // If no tracking exists, bail
        if (!$tracking) {
            return 0;
        }
        // Return current total
        return $tracking->total;
    }

    // ========================================================================= //

    /**
     * Get record's full namespace
     *
     * @return string
     */
    public function _recordNamespace($recordName): string
    {
        return 'doublesecretagency\\adwizard\\records\\'.$recordName;
    }

    /**
     * Get tracking record
     *
     * @return View|Click
     */
    public function _getTracking($record, $id, $year, $month)
    {
        return  $record::findOne([
            'adId'  => $id,
            'year'  => $year,
            'month' => $month,
        ]);
    }

    // ========================================================================= //

    /*
    // Check active status of ad
    private function _adStatus($ad)
    {
        $now       = new DateTime('now');
        $startDate = new DateTime($ad['startDate']);
        $endDate   = new DateTime($ad['endDate']);
        $notes  = '';
        $active = false;
        if (!$ad['enabled']) {
            $notes = 'Not enabled';
        } else if ($ad['startDate'] == $ad['endDate']) {
            $notes = 'Start & end date match';
        } else if ($endDate < $startDate) {
            $notes = 'Dates in wrong order';
        } else if ($now < $startDate) {
            $notes = 'Has not started yet';
        } else if ($endDate < $now) {
            $notes = 'Has already ended';
        } else if ($ad['maxViews'] && ($ad['maxViews'] <= $ad['totalViews'])) {
            $notes = 'Max impressions reached';
        } else {
            $notes = 'ACTIVE';
            $active = true;
        }
        return array(
            'active' => $active,
            'notes'  => $notes,
        );
    }
    */

}
