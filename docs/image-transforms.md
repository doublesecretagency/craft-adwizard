---
description: You can apply an image transform by specifying the transform when generating an Ad. Transforms can be specified in the CP, or defined in Twig.
---

# Image transforms

Ad Wizard now supports [image transforms](https://craftcms.com/docs/3.x/image-transforms.html), as part of the larger [attribute customization](/the-options-parameter/) functionality.

Within the `options` parameter of each function, you can specify an `image` object with something similar to this...

```js
{
    'image': {
        'transform': 'myTransform',
        'retina': true
    }
}
```

These are your `image` options:

| Options     | Type                | Description
|:------------|:--------------------|:------------
| `transform` | _string_ or _array_ | Specify the image transform to apply.
| `retina`    | _bool_              | Whether to render Ad for a retina screen.

## Basic Usage

A specific Ad...

```twig
{{ craft.adWizard.displayAd(99, {
    'image': {
        'transform': 'myTransform',
        'retina': true
    }
}) }}
```

Randomly selected Ad from a specified group...

```twig
{{ craft.adWizard.randomizeAdGroup('myGroup', {
    'image': {
        'transform': 'myTransform',
        'retina': true
    }
}) }}
```

## Complete Transform Support

Internally, Ad Wizard is using Craft's native transform technology. This means it's also possible to [define a transform in your template...](https://craftcms.com/docs/3.x/image-transforms.html#defining-transforms-in-your-templates)

```twig
{{ craft.adWizard.randomizeAdGroup('myGroup', {
    'image': {
        'transform': {
            mode: 'crop',
            width: 100,
            height: 100,
            quality: 75,
            position: 'top-center'
        }
    }
}) }}
```

## Retina Support

When using an image transform, it's also possible to specify a retina output.

```twig
{{ craft.adWizard.randomizeAdGroup('myGroup', {
    'image': {
        'transform': 'myTransform',
        'retina': true
    }
}) }}
```

If your image is using retina, you'll want to **double** the Image Transform size. For example, these two snippets will render the exact same image:

### As a normal asset (without Ad Wizard)

```twig
<img
  src="{{ asset.url('myTransform') }}"
  width="{{ asset.width('myTransform')/2 }}"
  height="{{ asset.height('myTransform')/2 }}"
/>
```

### As a retina Ad (with Ad Wizard)

```twig
{{ craft.adWizard.displayAd(99, {
    'image': {
        'transform': 'myTransform',
        'retina': true
    }
}) }}
```
