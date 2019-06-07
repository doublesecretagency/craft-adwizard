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

use yii\db\Exception;
use yii\web\NotFoundHttpException;

use Craft;
use craft\base\Component;
use craft\helpers\Template;
use craft\db\Query;
use craft\models\AssetTransform;

use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\elements\Ad;
use doublesecretagency\adwizard\records\AdGroup as AdGroupRecord;
use doublesecretagency\adwizard\web\assets\FrontEndAssets;

/**
 * Class Ads
 * @since 2.0.0
 */
class Ads extends Component
{

    private $_csrfIncluded = false;

    /**
     * Returns an ad by its ID.
     *
     * @param int $adId
     * @param int|null $siteId
     * @return Ad|null
     */
    public function getAdById(int $adId, int $siteId = null)
    {
        /** @var Ad|null $ad */
        $ad = Craft::$app->getElements()->getElementById($adId, Ad::class, $siteId);

        return $ad;
    }

    // ========================================================================= //

    // Move ads to a different group
    public function updateAdsGroup($adIds, $groupId)
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
        }
    }

    // Set field layout of all ads in group
    public function updateAdsLayout($fieldLayoutId, $groupId)
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
        }
    }

    // ========================================================================= //

    // Display ad
    public function renderAd($id, $transform = null, $retina = false)
    {
        $ad = $this->getAdById($id);
        return $this->_renderIndividualAd($ad, $transform, $retina);
    }

    // Display random ad from group
    public function renderRandomAdFromGroup($group, $transform = null, $retina = false)
    {
        $ad = $this->_getRandomAdFromGroup($group);
        return $this->_renderIndividualAd($ad, $transform, $retina);
    }

    // Render an individual ad
    private function _renderIndividualAd($ad, $transform = null, $retina = false)
    {
        // If no ad is specified, bail
        if (!$ad) {
            return false;
        }

        // If the ad is already a string, bail
        if (is_string($ad)) {
            return $ad;
        }

        // If ad can't be displayed, bail
        if (!$this->_displayAd($ad, $transform, $retina)) {
            return false;
        }

        // Track ad
        AdWizard::$plugin->adWizard_tracking->trackView($ad->id);

        // Render ad
        return Template::raw($ad->html);
    }

    // ========================================================================= //

    // Get individual ad via group
    private function _getRandomAdFromGroup($groupHandle)
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

        // Select random viable ad
        $ad = (new Query())
            ->select(['adwizard_ads.id'])
            ->from(['{{%adwizard_ads}} adwizard_ads'])
            ->innerJoin('{{%elements}} elements', '[[adwizard_ads.id]] = [[elements.id]]')
            ->where('[[elements.enabled]] = 1')
            ->andWhere(['groupId' => $groupRecord->id])
            ->andWhere('assetId IS NOT NULL')
            ->andWhere('(startDate  <= NOW()   ) OR (startDate IS NULL)')
            ->andWhere('(endDate    >= NOW()   ) OR (endDate   IS NULL)')
            ->andWhere('(totalViews <  maxViews) OR (maxViews  =  0)   ')
            ->orderBy('RAND()')
            ->one();

        // If no ads are available, bail
        if (!$ad || !is_array($ad)) {
            $this->err('No ads are available in the "'.$groupRecord->name.'" group.');
            return false;
        }

        // Return ad
        return $this->getAdById($ad['id']);
    }

    // Renders HTML of ad
    private function _displayAd(Ad $ad, $transform = null, $retina = false)
    {
        $asset = Craft::$app->getAssets()->getAssetById($ad->assetId);

        // If no asset, bail
        if (!$asset) {
            $this->err('No image specified for ad "'.$ad->title.'".');
            return false;
        }

        // If asset lacks a public URL, bail
        if (!$asset->getVolume()->hasUrls) {
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
                $width  = $width  / 2;
                $height = $height / 2;
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

        // CSRF
        if ($config->enableCsrfProtection === true) {
            if (!$this->_csrfIncluded) {
                $csrf = '
window.csrfTokenName = "'.$config->csrfTokenName.'";
window.csrfTokenValue = "'.Craft::$app->request->getCsrfToken().'";
';
                $view->registerJs($csrf, $view::POS_END);
                $this->_csrfIncluded = true;
            }
        }

        return true;
    }

    // Output error to console log
    private function err($error)
    {
        // Get view
        $view = Craft::$app->getView();

        $err = '[Ad Wizard] '.$error;
        $view->registerJs("console.log('{$err}')", $view::POS_END);
    }

}
