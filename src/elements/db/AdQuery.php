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

namespace doublesecretagency\adwizard\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use DateTime;
use DateTimeZone;
use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\models\AdGroup;

/**
 * Class AdQuery
 * @since 2.0.0
 */
class AdQuery extends ElementQuery
{

    // Properties
    // =========================================================================

    // General parameters
    // -------------------------------------------------------------------------

    /**
     * @var int|int[]|null The ad group ID(s) that the resulting ads must be in.
     */
    public $groupId;

    /**
     * @var int|null ID of ad image asset.
     */
    public $assetId;

    /**
     * @var mixed|null Datetime of beginning of ad run.
     */
    public $startDate;

    /**
     * @var mixed|null Datetime of end of ad run.
     */
    public $endDate;

    /**
     * @var int|null Total number of views the ad is allowed to received.
     */
    public $maxViews;

    /**
     * @var int|null Total number of views the ad has received.
     */
    public $totalViews;

    /**
     * @var int|null Total number of clicks the ad has received.
     */
    public $totalClicks;

    /**
     * @var bool Whether to exclude invalid ads.
     */
    protected $_excludeInvalid = false;

    // Public Methods
    // =========================================================================

    /**
     * Sets the [[groupId]] property based on a given group(s)’s handle(s).
     *
     * @param string|string[]|AdGroup|null $value The property value
     * @return static self reference
     */
    public function group($value)
    {
        if ($value instanceof AdGroup) {
            $this->groupId = $value->id;
        } else if ($value !== null) {
            $this->groupId = (new Query())
                ->select(['id'])
                ->from(['{{%adwizard_groups}}'])
                ->where(Db::parseParam('handle', $value))
                ->column();
        } else {
            $this->groupId = null;
        }

        return $this;
    }

    /**
     * Sets the [[groupId]] property.
     *
     * @param int|int[]|null $value The property value
     * @return static self reference
     */
    public function groupId($value)
    {
        $this->groupId = $value;
        return $this;
    }

    /**
     * Ensures that only valid ads will be included in the query.
     *
     * @return AdQuery $this
     */
    public function onlyValid()
    {
        $this->_excludeInvalid = true;
        return $this;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    protected function beforePrepare(): bool
    {
        // See if 'group' was set to an invalid handle
        if ($this->groupId === []) {
            return false;
        }

        $this->joinElementTable('adwizard_ads');

        $this->query->select([
            'adwizard_ads.groupId',
            'adwizard_ads.assetId',
            'adwizard_ads.url',
            'adwizard_ads.startDate',
            'adwizard_ads.endDate',
            'adwizard_ads.maxViews',
            'adwizard_ads.totalViews',
            'adwizard_ads.totalClicks',
        ]);

        if ($this->groupId) {
            $this->subQuery->andWhere(Db::parseParam('adwizard_ads.groupId', $this->groupId));
        }

        if ($this->_excludeInvalid) {

            // Current timestamp (in UTC)
            $current = new DateTime('now', new DateTimeZone('UTC'));
            $timestamp = $current->format('Y-m-d H:i:s');

            // Adjust subquery to filter out invalid ads
            $this->subQuery->andWhere('[[elements.enabled]] = 1');
            $this->subQuery->andWhere("([[adwizard_ads.startDate]]  <= '{$timestamp}') OR ([[adwizard_ads.startDate]] IS NULL)");
            $this->subQuery->andWhere("([[adwizard_ads.endDate]]    >= '{$timestamp}') OR ([[adwizard_ads.endDate]]   IS NULL)");
            $this->subQuery->andWhere('([[adwizard_ads.totalViews]] < [[adwizard_ads.maxViews]]) OR ([[adwizard_ads.maxViews]] = 0) OR ([[adwizard_ads.maxViews]] IS NULL)');

            if (AdWizard::$plugin->getSettings()->enableAdImages) {
                $this->subQuery->andWhere('[[adwizard_ads.assetId]] IS NOT NULL');
            }

            // Reset flag
            $this->_excludeInvalid = false;
        }

        return parent::beforePrepare();
    }

}
