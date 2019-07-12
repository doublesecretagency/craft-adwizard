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
use craft\base\Field;
use craft\base\FieldInterface;
use craft\elements\actions\Delete;
use craft\elements\actions\SetStatus;
use craft\elements\Asset;
use craft\elements\db\ElementQueryInterface;
use craft\errors\DeprecationException;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use craft\i18n\Locale;
use craft\models\FieldLayout;
use DateTime;
use DateTimeZone;
use doublesecretagency\adwizard\AdWizard;
use doublesecretagency\adwizard\elements\actions\ChangeAdGroup;
use doublesecretagency\adwizard\elements\db\AdQuery;
use doublesecretagency\adwizard\models\AdGroup;
use doublesecretagency\adwizard\records\Ad as AdRecord;
use Exception;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception as BaseException;
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
        // "All ads"
        $sources = [
            [
                'key'       => '*',
                'label'     => Craft::t('ad-wizard', 'All ads'),
                'data'      => ['handle' => ''],
                'criteria'  => ['status' => null],
                'hasThumbs' => true
            ]
        ];

        // Loop through remaining sources
        foreach (AdWizard::$plugin->groups->getAllGroups() as $group) {
            $sources[] = [
                'key'       => $group->handle,
                'label'     => Craft::t('site', $group->name),
                'data'      => ['handle' => $group->handle],
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
            'group'       => ['label' => Craft::t('ad-wizard', 'Group')],
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
            'group',
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
        return UrlHelper::cpUrl('ad-wizard/ads/'.$group->handle.'/'.$this->id);
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
     * @param array $options
     * @param bool $retinaDeprecated
     * @return bool|Markup
     * @throws DeprecationException
     * @throws BaseException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Throwable
     */
    public function displayAd($options = [], $retinaDeprecated = false)
    {
        // If using the old parameter structure
        if (AdWizard::$plugin->ads->oldParams($options)) {
            $docsUrl = 'https://www.doublesecretagency.com/plugins/ad-wizard/docs/the-options-parameter';
            $docsLink = "<a href=\"{$docsUrl}\" target=\"_blank\">Please consult the docs.</a>";
            $message = "The parameters of `ad.displayAd` have changed. {$docsLink}";
            Craft::$app->getDeprecator()->log('ad.displayAd', $message);
        }

        return AdWizard::$plugin->ads->renderAd($this->id, $options, $retinaDeprecated);
    }

    /**
     * Get image asset.
     *
     * @return Asset|null
     */
    public function image()
    {
        return Craft::$app->getAssets()->getAssetById($this->assetId);
    }

    // -------------------------------------------------------------------------

    /**
     * Returns the field with a given handle.
     *
     * @param string $handle
     * @return Field|FieldInterface|null
     */
    protected function fieldByHandle(string $handle)
    {
        return Craft::$app->getFields()->getFieldByHandle($handle);
    }

    /**
     * Gets field layout of ad (based on group).
     *
     * @inheritdoc
     * @return FieldLayout|null
     * @throws InvalidConfigException
     */
    public function getFieldLayout()
    {
        return parent::getFieldLayout() ?? $this->getGroup()->getFieldLayout();
    }

    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * @inheritDoc
     * @param string $attribute
     * @return string
     * @throws InvalidConfigException
     * @throws Exception
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {

            case 'group':

                $group = $this->getGroup();

                // If no group, bail
                if (!$group) {
                    return '';
                }

                return $group->name;

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

                $date = $this->_normalizeDate($date);

                // If still no date, bail
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

        // If layout exists, return the value of matching field
        if ($layout = $this->getFieldLayout()) {
            foreach ($layout->getFields() as $field) {
                if ("field:{$field->id}" == $attribute) {
                    return parent::tableAttributeHtml($attribute);
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getEditorHtml(): string
    {
        $view = Craft::$app->getView();


        $html = $view->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => Craft::t('app', 'Title'),
//                'siteId' => $this->siteId,
                'id' => 'title',
                'name' => 'title',
                'value' => $this->title,
                'errors' => $this->getErrors('title'),
                'first' => true,
                'autofocus' => true,
                'required' => true,
            ]
        ]);

        $html .= $view->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => Craft::t('app', 'URL'),
//                'siteId' => $this->siteId,
                'id' => 'url',
                'name' => 'url',
                'value' => $this->url,
                'errors' => $this->getErrors('url'),
                'required' => true,
            ]
        ]);

        $html .= $view->renderTemplateMacro('_includes/forms', 'dateTimeField', [
            [
                'label' => Craft::t('ad-wizard', 'Beginning of Run'),
                'id' => 'startDate',
                'name' => 'startDate',
                'value' => $this->startDate,
                'errors' => $this->getErrors('startDate'),
            ]
        ]);

        $html .= $view->renderTemplateMacro('_includes/forms', 'dateTimeField', [
            [
                'label' => Craft::t('ad-wizard', 'End of Run'),
                'id' => 'endDate',
                'name' => 'endDate',
                'value' => $this->endDate,
                'errors' => $this->getErrors('endDate'),
            ]
        ]);

        $html .= $view->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => Craft::t('ad-wizard', 'Max Views Allowed'),
                'instructions' => Craft::t('ad-wizard', '(0 = unlimited)'),
                'id' => 'maxViews',
                'name' => 'maxViews',
                'value' => $this->maxViews,
                'errors' => $this->getErrors('maxViews'),
                'size' => 3,
            ]
        ]);

        // Render the custom fields
        $html .= parent::getEditorHtml();

        return $html;
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * @inheritDoc
     * @throws BaseException
     * @throws Exception
     */
    public function afterSave(bool $isNew)
    {
        // Get the ad record
        if (!$isNew) {
            $record = AdRecord::findOne($this->id);

            if (!$record) {
                throw new BaseException('Invalid ad ID: '.$this->id);
            }
        } else {
            $record = new AdRecord();
            $record->id = $this->id;
        }

        $record->groupId   = $this->groupId;
        $record->assetId   = $this->assetId;
        $record->url       = $this->url;
        $record->startDate = $this->_normalizeDate($this->startDate);
        $record->endDate   = $this->_normalizeDate($this->endDate);
        $record->maxViews  = $this->maxViews;

        $record->save(false);

        parent::afterSave($isNew);
    }

    // Private Methods
    // =========================================================================

    /**
     * Properly format datetime for database.
     *
     * @param $date
     * @return DateTime|mixed
     * @throws Exception
     */
    private function _normalizeDate($date)
    {
        // If it's an array, create a DateTime object
        if (is_array($date) && isset($date['timezone'])) {

            // If no date or time, bail
            if (!$date['date'] && !$date['time']) {
                return null;
            }

            // Get datetime
            $datetime = new DateTime(
                $date['date'].' '.$date['time'],
                new DateTimeZone($date['timezone'])
            );

            // If datetime was determined, return formatted string
            if ($datetime) {
                return DateTimeHelper::toIso8601($datetime);
            }

        }

        // Return unchanged value
        return $date;
    }

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
