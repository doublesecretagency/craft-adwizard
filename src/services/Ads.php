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
use craft\base\ElementInterface;
use craft\base\Volume;
use craft\db\Query;
use craft\helpers\Template;
use DateTime;
use DateTimeZone;
use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\models\Config;
use doublesecretagency\adwizard\records\AdGroup as AdGroupRecord;
use doublesecretagency\adwizard\web\assets\FrontEndAssets;
use Exception;
use Throwable;
use Twig\Markup;
use yii\base\Exception as BaseException;
use yii\base\InvalidConfigException;
use yii\db\Exception as DbException;
use yii\web\NotFoundHttpException;

/**
 * Class Ads
 * @since 2.0.0
 */
class Ads extends Component
{

    /**
     * @var bool Whether the CSRF token has already been set via JavaScript.
     */
    private $_csrfIncluded = false;

    /**
     * Returns an ad by its ID.
     *
     * @param int $adId
     * @param int|null $siteId
     * @return ElementInterface|null
     */
    public function getAdById(int $adId, int $siteId = null)
    {
        return Craft::$app->getElements()->getElementById($adId, Ad::class, $siteId);
    }

    // ========================================================================= //

    /**
     * Move ads to a different group.
     *
     * @param array $adIds
     * @param int $groupId
     */
    public function updateAdsGroup(array $adIds, int $groupId)
    {
        try {
            Craft::$app->getDb()->createCommand()
                ->update(
                    '{{%adwizard_ads}}',
                    ['groupId' => $groupId],
                    ['id' => $adIds]
                )
                ->execute();

            $fieldLayoutId = (new Query())
                ->select(['fieldLayoutId'])
                ->from(['{{%adwizard_groups}}'])
                ->where(['id' => $groupId])
                ->scalar();

            $this->updateAdsLayout($fieldLayoutId, $groupId);
        } catch (DbException $e) {
            // Do nothing
        }
    }

    /**
     * Set field layout of all ads in group.
     *
     * @param int|null $fieldLayoutId
     * @param int $groupId
     */
    public function updateAdsLayout($fieldLayoutId, int $groupId)
    {
        // Get ads in group
        $adIds = (new Query())
            ->select(['id'])
            ->from(['{{%adwizard_ads}}'])
            ->where(['groupId' => $groupId])
            ->column();

        try {
            Craft::$app->getDb()->createCommand()
                ->update(
                    '{{%elements}}',
                    ['fieldLayoutId' => $fieldLayoutId],
                    [
                        'id'   => $adIds,
                        'type' => Ad::class,
                    ]
                )
                ->execute();
        } catch (DbException $e) {
            // Do nothing
        }
    }

    // ========================================================================= //

    /**
     * Display ad.
     *
     * @param int $id
     * @param array $options
     * @param bool $retinaDeprecated
     * @return bool|Markup
     * @throws BaseException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Throwable
     */
    public function renderAd(int $id, $options = [], bool $retinaDeprecated = false)
    {
        $ad = $this->_getSingleAd($id);
        return $this->_renderIndividualAd($ad, $options, $retinaDeprecated);
    }

    /**
     * Display random ad from group.
     *
     * @param string $group
     * @param array $options
     * @param bool $retinaDeprecated
     * @return bool|Markup
     * @throws BaseException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Throwable
     */
    public function renderRandomAdFromGroup(string $group, $options = [], bool $retinaDeprecated = false)
    {
        $ad = $this->_getRandomAdFromGroup($group);
        return $this->_renderIndividualAd($ad, $options, $retinaDeprecated);
    }

    /**
     * Render an individual ad.
     *
     * @param $ad
     * @param array $options
     * @param bool $retinaDeprecated
     * @return bool|Markup
     * @throws BaseException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Throwable
     */
    private function _renderIndividualAd($ad, $options = [], bool $retinaDeprecated = false)
    {
        // If no ad is specified, bail
        if (!$ad) {
            return false;
        }

//        // If the ad is already a string, bail
//        if (is_string($ad)) {
//            return $ad;
//        }

        // Get raw HTML of ad
        $html = $this->_getAdHtml($ad, $options, $retinaDeprecated);

        // If ad can't be displayed, bail
        if (!$html) {
            return false;
        }

        // Track ad
        AdWizard::$plugin->tracking->trackView($ad->id);

        // Render ad
        return Template::raw($html);
    }

    // ========================================================================= //

    /**
     * Get ID of valid ad.
     *
     * @param array $conditions
     * @return bool|false|string|null
     * @throws Exception
     */
    public function getValidAdId(array $conditions)
    {
        // Current timestamp (in UTC)
        $current = new DateTime('now', new DateTimeZone('UTC'));
        $timestamp = $current->format('Y-m-d H:i:s');

        $dbDriver = Craft::$app->db->getDriverName();

        if ($dbDriver == 'pgsql') {
            // Return valid ad ID when using PostgreSQL
            return (new Query())
                ->select(['ads.id'])
                ->from(['{{%adwizard_ads}} ads'])
                ->innerJoin('{{%elements}} elements', '[[ads.id]] = [[elements.id]]')
                ->where($conditions)
                ->andWhere('[[elements.enabled]] = true')
                ->andWhere('[[ads.assetId]] IS NOT NULL')
                ->andWhere("([[ads.startDate]]  <= '{$timestamp}') OR ([[ads.startDate]] IS NULL)")
                ->andWhere("([[ads.endDate]]    >= '{$timestamp}') OR ([[ads.endDate]]   IS NULL)")
                ->andWhere('([[ads.totalViews]] < [[ads.maxViews]]) OR ([[ads.maxViews]] = 0) OR ([[ads.maxViews]] IS NULL)')
                ->orderBy('random()')
                ->scalar();            
        } else {

            // Return valid ad ID
            return (new Query())
                ->select(['ads.id'])
                ->from(['{{%adwizard_ads}} ads'])
                ->innerJoin('{{%elements}} elements', '[[ads.id]] = [[elements.id]]')
                ->where($conditions)
                ->andWhere('[[elements.enabled]] = 1')
                ->andWhere('[[ads.assetId]] IS NOT NULL')
                ->andWhere("([[ads.startDate]]  <= '{$timestamp}') OR ([[ads.startDate]] IS NULL)")
                ->andWhere("([[ads.endDate]]    >= '{$timestamp}') OR ([[ads.endDate]]   IS NULL)")
                ->andWhere('([[ads.totalViews]] < [[ads.maxViews]]) OR ([[ads.maxViews]] = 0) OR ([[ads.maxViews]] IS NULL)')
                ->orderBy('RAND()')
                ->scalar();
        }
    }

    /**
     * Get a single specific ad.
     *
     * @param int $id
     * @return ElementInterface|bool|null
     * @throws Exception
     */
    private function _getSingleAd($id)
    {
        // If invalid ad ID, bail
        if (!$id || !is_int($id)) {
            $this->err('Invalid ad ID specified.');
            return false;
        }

        // Ensure ad ID is valid (not disabled/expired/maxed)
        $adId = $this->getValidAdId(['[[ads.id]]' => $id]);

        // If ad isn't available, bail
        if (!$adId) {
            $this->err('Ad with ID of '.$id.' is not valid.');
            return false;
        }

        // Return ad
        return $this->getAdById($adId);
    }

    /**
     * Get individual ad via group.
     *
     * @param string $groupHandle
     * @return ElementInterface|bool|null
     * @throws Exception
     */
    private function _getRandomAdFromGroup(string $groupHandle)
    {
        // If no group handle is specified, bail
        if (!$groupHandle) {
            $this->err('Please specify an ad group.');
            return false;
        }

        // Get group record
        $groupRecord = AdGroupRecord::findOne([
            'handle' => $groupHandle,
        ]);

        // If no group record exists, bail
        if (!$groupRecord) {
            $this->err('"'.$groupHandle.'" is not a valid group handle.');
            return false;
        }

        // Get a random ad ID from specified ad group
        $adId = $this->getValidAdId(['[[ads.groupId]]' => $groupRecord->id]);

        // If no ads are available, bail
        if (!$adId) {
            $this->err('No ads are available in the "'.$groupRecord->name.'" group.');
            return false;
        }

        // Return ad
        return $this->getAdById($adId);
    }

    /**
     * Whether the code is using a deprecated method.
     *
     * @param $options
     * @return bool
     */
    public function oldParams($options): bool
    {
        // No options defined, not using old parameters
        if (empty($options)) {
            return false;
        }

        // Using pre-defined transform
        if (is_string($options)) {
            return true;
        }

        // Using dynamic transform
        if (is_array($options) && !isset($options['image']) && !isset($options['attr']) && !isset($options['js'])) {
            return true;
        }

        return false;
    }

    /**
     * Load CSRF token data into JavaScript.
     *
     * @throws InvalidConfigException
     */
    private function _loadCsrf()
    {
        // Get services
        $view = Craft::$app->getView();
        $config = Craft::$app->getConfig()->getGeneral();

        // Register assets
        $view->registerAssetBundle(FrontEndAssets::class);

        // Whether to include CSRF
        $includeCsrf = ($config->enableCsrfProtection === true);

        // CSRF
        if ($includeCsrf && !$this->_csrfIncluded) {
            $csrf = '
window.csrfTokenName = "'.$config->csrfTokenName.'";
window.csrfTokenValue = "'.Craft::$app->request->getCsrfToken().'";
';
            $view->registerJs($csrf, $view::POS_END);
            $this->_csrfIncluded = true;
        }
    }

    /**
     * Configures and returns HTML of ad.
     *
     * @param Ad $ad
     * @param array $options
     * @param bool $retinaDeprecated
     * @return bool|string
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws BaseException
     */
    private function _getAdHtml(Ad $ad, $options = [], bool $retinaDeprecated = false)
    {
        // If no URL, bail
        if (!$ad->url) {
            $this->err('No URL specified for ad "'.$ad->title.'".');
            return false;
        }

        // If no asset ID, bail
        if (!$ad->assetId) {
            $this->err('No image specified for ad "'.$ad->title.'".');
            return false;
        }

        // Get asset
        $asset = Craft::$app->getAssets()->getAssetById($ad->assetId);

        // If no asset, bail
        if (!$asset) {
            $this->err('No image specified for ad "'.$ad->title.'".');
            return false;
        }

        // Get asset volume
        /** @var Volume $volume */
        $volume = $asset->getVolume();

        // If asset lacks a public URL, bail
        if (!$volume->hasUrls) {
            $this->err('Asset image for ad "'.$ad->title.'" belongs to a volume with no public URL.');
            return false;
        }

        // If using the old parameter structure
        if ($this->oldParams($options)) {

            // Convert old structure to new structure
            $options = [
                'image' => [
                    'transform' => $options,
                    'retina' => $retinaDeprecated,
                ]
            ];

        }

        // Load CSRF token data into JS
        $this->_loadCsrf();

        // Configure ad layout from options
        $config = new Config($ad, $asset, $options);

        // Return configured HTML
        return $config->getHtml();
    }

    /**
     * Output error to console log.
     *
     * @param string $error
     */
    private function err(string $error)
    {
        // Get view
        $view = Craft::$app->getView();

        $err = '[Ad Wizard] '.$error;
        $view->registerJs("console.log('{$err}')", $view::POS_END);
    }

}
