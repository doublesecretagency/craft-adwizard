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

use craft\base\Component;
use doublesecretagency\adwizard\records\Ad as AdRecord;
use doublesecretagency\adwizard\records\Click;
use doublesecretagency\adwizard\records\View;

/**
 * Class Tracking
 * @since 2.0.0
 */
class Tracking extends Component
{

    /**
     * Track view.
     *
     * @param int $id
     * @return bool
     */
    public function trackView(int $id): bool
    {
        $this->_incrementTotal($id, 'totalViews');
        return $this->_incrementDay($id, 'View');
    }

    /**
     * Track click.
     *
     * @param int $id
     * @return bool
     */
    public function trackClick(int $id): bool
    {
        $this->_incrementTotal($id, 'totalClicks');
        return $this->_incrementDay($id, 'Click');
    }

    /**
     * Increment total overall value.
     *
     * @param int $id
     * @param string $field
     * @return bool
     */
    private function _incrementTotal(int $id, string $field): bool
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

    /**
     * Total views in specified month.
     *
     * @param int $id
     * @param int $year
     * @param int $month
     * @return int
     */
    public function monthTotalViews(int $id, int $year, int $month): int
    {
        return $this->_getMonthTotal($id, 'View', $year, $month);
    }

    /**
     * Total clicks in specified month.
     *
     * @param int $id
     * @param int $year
     * @param int $month
     * @return int
     */
    public function monthTotalClicks(int $id, int $year, int $month): int
    {
        return $this->_getMonthTotal($id, 'Click', $year, $month);
    }

    // ========================================================================= //

    /**
     * Increment total daily value.
     *
     * @param int $id
     * @param string $recordName
     * @return bool
     */
    private function _incrementDay(int $id, string $recordName): bool
    {
        // Date info
        $year   = date('Y');
        $month  = date('n');
        $day    = 'day'.date('j');

        // Get tracking info
        $record = $this->_recordNamespace($recordName);
        $tracking = $this->_getTracking($record, $id, $year, $month);

        // If tracking exists, update record
        if ($tracking) {
            return $tracking->updateCounters([
                $day => 1,
                'total' => 1,
            ]);
        }

        // Create a new tracking record
        /** @var View|Click $tracking */
        $tracking = new $record;
        $tracking->adId  = $id;
        $tracking->year  = $year;
        $tracking->month = $month;
        $tracking->$day  = 1;
        $tracking->total = 1;
        return $tracking->save();
    }

    // ========================================================================= //

    /**
     * Get total number of views/clicks per month.
     *
     * @param int $id
     * @param string $recordName
     * @param int $year
     * @param int $month
     * @return int
     */
    public function _getMonthTotal(int $id, string $recordName, int $year, int $month): int
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
     * Get record's full namespace.
     *
     * @param string $recordName
     * @return string
     */
    public function _recordNamespace(string $recordName): string
    {
        return 'doublesecretagency\\adwizard\\records\\'.$recordName;
    }

    /**
     * Get tracking record.
     *
     * @param string $record
     * @param int $id
     * @param int $year
     * @param int $month
     * @return View|Click|null
     */
    public function _getTracking(string $record, int $id, int $year, int $month): View|Click|null
    {
        /** @var View|Click $record */
        return $record::findOne([
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
        return [
            'active' => $active,
            'notes'  => $notes,
        ];
    }
    */

}
