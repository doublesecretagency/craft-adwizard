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

namespace doublesecretagency\adwizard\elements;

use Craft;
use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\actions\SetStatus;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use craft\i18n\Locale;
use DateTime;
use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\elements\actions\ChangeAdGroup;
use doublesecretagency\adwizard\elements\db\AdQuery;
use doublesecretagency\adwizard\models\AdGroup;
use doublesecretagency\adwizard\records\Ad as AdRecord;
use Twig\Markup;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\web\NotFoundHttpException;

/**
 * Class Ad
 * @since 2.0.0
 */
class Ad extends Element
{

    // Static
    // =========================================================================

    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('ad-wizard', 'Ad');
    }

    /**
     * @inheritDoc
     */
    public static function refHandle()
    {
        return 'ad';
    }

    /**
     * @inheritDoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     * @return AdQuery The newly created [[AdQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new AdQuery(static::class);
    }

    /**
     * @inheritDoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key'       => '*',
                'label'     => Craft::t('ad-wizard', 'All ads'),
                'criteria'  => ['status' => null],
                'hasThumbs' => true
            ]
        ];

        foreach (AdWizard::$plugin->groups->getAllGroups() as $group) {
            $sources[] = [
                'key'       => 'group:'.$group->id,
                'label'     => Craft::t('site', $group->name),
                'criteria'  => ['groupId' => $group->id],
                'hasThumbs' => true
            ];
        }

        return $sources;
    }

    /**
     * @inheritDoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        // Set Status
        $actions[] = SetStatus::class;

        // Change Ad Group
        $actions[] = ChangeAdGroup::class;

        // Delete
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('ad-wizard', 'Are you sure you want to delete the selected ads?'),
            'successMessage' => Craft::t('ad-wizard', 'Ads deleted.'),
        ]);

        return $actions;
    }

    /**
     * @inheritDoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['title', 'url'];
    }

    /**
     * @inheritDoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title'       => Craft::t('app', 'Title'),
            'url'         => Craft::t('app', 'URL'),
            'startDate'   => Craft::t('ad-wizard', 'Start Date'),
            'endDate'     => Craft::t('ad-wizard', 'End Date'),
            'maxViews'    => Craft::t('ad-wizard', 'Max Views'),
            'totalClicks' => Craft::t('ad-wizard', 'Total Clicks'),
            'totalViews'  => Craft::t('ad-wizard', 'Total Views'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'title'       => ['label' => Craft::t('app', 'Title')],
            'url'         => ['label' => Craft::t('app', 'URL')],
            'startDate'   => ['label' => Craft::t('ad-wizard', 'Start Date')],
            'endDate'     => ['label' => Craft::t('ad-wizard', 'End Date')],
            'maxViews'    => ['label' => Craft::t('ad-wizard', 'Max Views')],
            'totalClicks' => ['label' => Craft::t('ad-wizard', 'Total Clicks')],
            'totalViews'  => ['label' => Craft::t('ad-wizard', 'Total Views')],
        ];

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'url',
            'startDate',
            'endDate',
            'maxViews',
            'totalClicks',
            'totalViews',
        ];
    }

    // Properties
    // =========================================================================

    /**
     * @var int $groupId ID of group which contains the ad.
     */
    public $groupId;

    /**
     * @var int|null $assetId ID of asset which ad contains.
     */
    public $assetId;

    /**
     * @var string $url URL of ad target.
     */
    public $url = '';

    /**
     * @var DateTime $startDate Date ad will begin its run.
     */
    public $startDate;

    /**
     * @var DateTime $endDate Date ad will end its run.
     */
    public $endDate;

    /**
     * @var int $maxViews Maximum number of ad views allowed.
     */
    public $maxViews = 0;

    /**
     * @var int $totalViews Total number of times the ad has been viewed.
     */
    public $totalViews = 0;

    /**
     * @var int $totalClicks Total number of times the ad has been clicked.
     */
    public $totalClicks = 0;

    /**
     * @var string $filepath Path to asset file.
     */
    public $filepath = '';

    /**
     * @var int $width Width of asset file.
     */
    public $width = 0;

    /**
     * @var int $height Height of asset file.
     */
    public $height = 0;

    /**
     * @var string $html Fully prepared ad HTML.
     */
    public $html = '';

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'startDate';
        $attributes[] = 'endDate';
        return $attributes;
    }

    /**
     * @inheritDoc
     */
    public function getIsEditable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCpEditUrl(): string
    {
        // Get ad group
        /** @var AdGroup $group */
        $group = AdWizard::$plugin->groups->getGroupById($this->groupId);

        // Return edit url
        return UrlHelper::cpUrl('ad-wizard/'.$group->handle.'/'.$this->id);
    }

    /**
     * @inheritDoc
     * @param int $size
     * @return string|null
     * @throws NotSupportedException
     */
    public function getThumbUrl(int $size)
    {
        // If no asset ID, bail
        if (!$this->assetId) {
            return $this->_defaultThumb();
        }

        // Get asset
        $asset = Craft::$app->getAssets()->getAssetById($this->assetId);

        // If no asset, bail
        if (!$asset) {
            return $this->_defaultThumb();
        }

        // Return thumbnail URL
        return Craft::$app->getAssets()->getThumbUrl($asset, $size, $size);
    }

    /**
     * Returns the ad's group.
     *
     * @return AdGroup
     * @throws InvalidConfigException if [[groupId]] is missing or invalid
     */
    public function getGroup(): AdGroup
    {
        if ($this->groupId === null) {
            throw new InvalidConfigException('Ad is missing its group ID');
        }

        $group = AdWizard::$plugin->groups->getGroupById($this->groupId);

        if ($group === null) {
            throw new InvalidConfigException('Invalid ad group ID: '.$this->groupId);
        }

        return $group;
    }

    /**
     * Display this ad.
     *
     * @param null $transform
     * @param bool $retina
     * @return bool|Markup
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function displayAd($transform = null, $retina = false)
    {
        return AdWizard::$plugin->ads->renderAd($this->id, $transform, $retina);
    }

    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * @inheritDoc
     * @param string $attribute
     * @return string
     * @throws InvalidConfigException
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {

            case 'url':
                $url = $this->$attribute;

                // If no URL, bail
                if (!$url) {
                    return '';
                }

                $value = $url;

                // Add some <wbr> tags in there so it doesn't all have to be on one line
                $find = ['/'];
                $replace = ['/<wbr>'];

                $wordSeparator = Craft::$app->getConfig()->getGeneral()->slugWordSeparator;

                if ($wordSeparator) {
                    $find[] = $wordSeparator;
                    $replace[] = $wordSeparator.'<wbr>';
                }

                $value = str_replace($find, $replace, $value);
                return '<a href="'.$url.'" target="_blank" class="go"><span dir="ltr">'.$value.'</span></a>';

            case 'startDate':
            case 'endDate':
                $date = $this->$attribute;

                // If no date object, bail
                if (!$date) {
                    return '';
                }

                return Craft::$app->getFormatter()->asDate($date, Locale::LENGTH_SHORT);

            case 'totalClicks':
            case 'totalViews':
                return $this->$attribute;

            case 'maxViews':
                return ($this->$attribute ?: '');

        }

        return parent::tableAttributeHtml($attribute);
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * @inheritDoc
     * @throws Exception if ad ID is invalid
     */
    public function afterSave(bool $isNew)
    {
        // Get the ad record
        if (!$isNew) {
            $record = AdRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid ad ID: '.$this->id);
            }
        } else {
            $record = new AdRecord();
            $record->id = $this->id;
        }

        $record->groupId   = $this->groupId;
        $record->assetId   = $this->assetId;
        $record->url       = $this->url;
        $record->startDate = $this->startDate;
        $record->endDate   = $this->endDate;
        $record->maxViews  = $this->maxViews;

        $record->save(false);

        parent::afterSave($isNew);
    }

    // Private Methods
    // =========================================================================

    /**
     * Default thumbnail for missing images.
     *
     * @return string Path to "broken image" SVG.
     */
    private function _defaultThumb(): string
    {
        return Craft::$app->getAssetManager()->getPublishedUrl('@app/icons', true, 'broken-image.svg');
    }

}
