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
use craft\models\AssetTransform;
use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\records\AdGroup as AdGroupRecord;
use doublesecretagency\adwizard\web\assets\FrontEndAssets;
use Twig\Markup;
use yii\base\InvalidConfigException;
use yii\db\Exception;
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
        } catch (Exception $e) {
            // Do nothing
        }
    }

    /**
     * Set field layout of all ads in group.
     *
     * @param int $fieldLayoutId
     * @param int $groupId
     */
    public function updateAdsLayout(int $fieldLayoutId, int $groupId)
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
        } catch (Exception $e) {
            // Do nothing
        }
    }

    // ========================================================================= //

    /**
     * Display ad.
     *
     * @param int $id
     * @param string|array|null $transform
     * @param bool $retina
     * @return Markup|bool
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public function renderAd(int $id, $transform = null, bool $retina = false)
    {
        $ad = $this->_getSingleAd($id);
        return $this->_renderIndividualAd($ad, $transform, $retina);
    }

    /**
     * Display random ad from group.
     *
     * @param string $group
     * @param string|array|null $transform
     * @param bool $retina
     * @return Markup|bool
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public function renderRandomAdFromGroup(string $group, $transform = null, bool $retina = false)
    {
        $ad = $this->_getRandomAdFromGroup($group);
        return $this->_renderIndividualAd($ad, $transform, $retina);
    }

    /**
     * Render an individual ad.
     *
     * @param $ad
     * @param string|array|null $transform
     * @param bool $retina
     * @return Markup|bool
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    private function _renderIndividualAd($ad, $transform = null, bool $retina = false)
    {
        // If no ad is specified, bail
        if (!$ad) {
            return false;
        }

//        // If the ad is already a string, bail
//        if (is_string($ad)) {
//            return $ad;
//        }

        // If ad can't be displayed, bail
        if (!$this->_displayAd($ad, $transform, $retina)) {
            return false;
        }

        // Track ad
        AdWizard::$plugin->tracking->trackView($ad->id);

        // Render ad
        return Template::raw($ad->html);
    }

    // ========================================================================= //

    /**
     * Get ID of valid ad.
     *
     * @param array $conditions
     * @return bool|false|string|null
     */
    public function getValidAdId(array $conditions)
    {
         return (new Query())
            ->select(['ads.id'])
            ->from(['{{%adwizard_ads}} ads'])
            ->innerJoin('{{%elements}} elements', '[[ads.id]] = [[elements.id]]')
            ->where($conditions)
            ->andWhere('[[elements.enabled]] = 1')
            ->andWhere('[[ads.assetId]] IS NOT NULL')
            ->andWhere('([[ads.startDate]]  <= NOW()) OR ([[ads.startDate]] IS NULL)')
            ->andWhere('([[ads.endDate]]    >= NOW()) OR ([[ads.endDate]]   IS NULL)')
            ->andWhere('([[ads.totalViews]] < [[ads.maxViews]]) OR ([[ads.maxViews]] = 0)')
            ->orderBy('RAND()')
            ->scalar();
    }

    /**
     * Get a single specific ad.
     *
     * @param int $id
     * @return ElementInterface|bool|null
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
     * Renders HTML of ad.
     *
     * @param Ad $ad
     * @param string|array|null $transform
     * @param bool $retina
     * @return bool
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    private function _displayAd(Ad $ad, $transform = null, bool $retina = false): bool
    {
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

        // If no URL, bail
        if (!$ad->url) {
            $this->err('No URL specified for ad "'.$ad->title.'".');
            return false;
        }

        // Get image transform
        if (is_string($transform)) {
            $t = clone Craft::$app->getAssetTransforms()->getTransformByHandle($transform);
            if (!$t) {
                throw new NotFoundHttpException('Transform not found');
            }
        } else if (is_array($transform)) {
            $t = new AssetTransform($transform);
        } else {
            $t = false;
        }

        // If transform exists, apply it
        if ($t) {
            $url    = $asset->getUrl($t);
            $width  = $asset->getWidth($t);
            $height = $asset->getHeight($t);
            // If retina, make <img> tag smaller
            if (true === $retina) {
                $width  /= 2;
                $height /= 2;
            }
        } else {
            $url    = $asset->getUrl();
            $width  = $asset->getWidth();
            $height = $asset->getHeight();
        }

        $onclick = "adWizard.click({$ad->id}, '{$ad->url}')";

        $ad->html = PHP_EOL
            .'<img'
            .' src="'.$url.'"'
            .' width="'.$width.'"'
            .' height="'.$height.'"'
            .' class="adWizard-ad"'
            .' style="cursor:pointer"'
            .' onclick="'.$onclick.'"'
            .'/>';

        // Get view
        $view = Craft::$app->getView();

        // Register assets
        $view->registerAssetBundle(FrontEndAssets::class);

        // Get config settings
        $config = Craft::$app->getConfig()->getGeneral();

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

        return true;
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
