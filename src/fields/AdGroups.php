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

namespace doublesecretagency\adwizard\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Html;
use doublesecretagency\adwizard\AdWizard;
use yii\db\Schema;

/**
 * Class AdGroups
 * @since 3.1.0
 */
class AdGroups extends Field
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ad-wizard', 'Ad Group');
    }

//    /**
//     * @inheritdoc
//     */
//    public static function defaultSelectionLabel(): string
//    {
//        return Craft::t('ad-wizard', 'Select an ad group');
//    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        $groups = AdWizard::$plugin->groups->getAllGroups();

        $options = [
            '' => Craft::t('ad-wizard', 'Select an Ad Group'),
        ];

        foreach ($groups as $group) {
            $options[$group->handle] = $group->name;
        }

        $id = Html::id($this->handle);
//        $nameSpacedId = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate('ad-wizard/_field/input', [
            'id' => $id,
            'name' => $this->handle,
            'value' => $value,
            'options' => $options,
        ]);
    }
}
