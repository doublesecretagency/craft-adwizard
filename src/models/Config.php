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

namespace doublesecretagency\adwizard\models;

use Craft;
use craft\base\Model;
use craft\elements\Asset;
use craft\models\ImageTransform;
use doublesecretagency\adwizard\elements\Ad;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

/**
 * Class Config
 * @since 2.1.0
 *
 * @property string $html
 */
class Config extends Model
{

    /**
     * @var Ad|null
     */
    public ?Ad $ad = null;

    /**
     * @var Asset|null
     */
    public ?Asset $asset = null;

    /**
     * @var array Image manipulation options.
     */
    public array $image = [
        'transform' => null,
        'retina' => false,
    ];

    /**
     * @var array HTML attribute options.
     */
    public array $attr = [
        'class' => 'adWizard-ad',
        'style' => 'cursor:pointer',
    ];

    /**
     * @var array JavaScript trigger options.
     */
    public array $js = [
        'click' => 'adWizard.click({id}, \'{url}\')',
    ];

    /**
     * Config constructor.
     *
     * @param Ad $ad
     * @param Asset $asset
     * @param array $options
     * @param array $config
     */
    public function __construct(Ad $ad, Asset $asset, array $options = [], array $config = [])
    {
        // Set ad & asset
        $this->ad = $ad;
        $this->asset = $asset;

        // Merge options over default configuration
        $properties = ['image','attr','js'];
        foreach ($properties as $prop) {

            // If it's not being overridden, skip
            if (!isset($options[$prop])) {
                continue;
            }

            // Merge custom options into default values
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $this->{$prop} = array_merge($this->{$prop}, $options[$prop]);
        }

        // Call parent
        parent::__construct($config);
    }

    // ========================================================================= //

    /**
     * Get raw <img> tag.
     *
     * @return string
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws Exception
     */
    public function getHtml(): string
    {
        $this->_parseJs();
        $this->_parseTransform();

        // Generate <img> attributes
        $attributes = '';
        foreach ($this->attr as $key => $value) {
            $attributes .= "{$key}=\"{$value}\" ";
        }

        // Compile <img> tag with attributes
        /** @noinspection HtmlRequiredAltAttribute */
        $tag = "<img {$attributes}/>";

        // Return <img> tag
        return PHP_EOL.Craft::$app->getView()->renderObjectTemplate($tag, $this->ad);
    }

    // ========================================================================= //

    /**
     * Parse JS tags into HTML attributes.
     */
    private function _parseJs(): void
    {
        foreach ($this->js as $trigger => $code) {
            $this->attr["on{$trigger}"] = $code;
        }
    }

    /**
     * Process transform
     *
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    private function _parseTransform(): void
    {
        if (is_string($this->image['transform'])) {

            // Get pre-defined transform
            $transform = clone Craft::$app->getAssetTransforms()->getTransformByHandle($this->image['transform']);

            if (!$transform) {
                throw new NotFoundHttpException('Transform not found');
            }

        } else if (is_array($this->image['transform']) && !empty($this->image['transform'])) {

            // Get dynamic transform
            $transform = new ImageTransform($this->image['transform']);

        } else {

            // No transform
            $transform = false;

        }

        // If transform exists
        if ($transform) {

            // Apply transform
            $url    = $this->asset->getUrl($transform);
            $width  = $this->asset->getWidth($transform);
            $height = $this->asset->getHeight($transform);

            // If retina, make <img> tag smaller
            if (true === $this->image['retina']) {
                $width  /= 2;
                $height /= 2;
            }

        } else {

            // No transform
            $url    = $this->asset->getUrl();
            $width  = $this->asset->getWidth();
            $height = $this->asset->getHeight();

        }

        // Set new image source & dimensions
        $this->attr = array_merge([
            'src'    => $url,
            'width'  => $width,
            'height' => $height,
        ], $this->attr);
    }

}
