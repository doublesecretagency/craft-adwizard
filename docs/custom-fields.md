---
description: As of Ad Wizard 3, it's now possible to use custom fields with your Ads. These custom fields can be absolutely anything that you want.
---

# Custom Fields

As of Ad Wizard 3, it's now possible to use custom fields with your Ads. These custom fields can be absolutely anything that you want.

<img :src="$withBase('/images/adwizard-dynamic-fields.png')" class="dropshadow" alt="" style="max-width:600px">

## Available as Options

In the example above, the "Alt Tag" and "Title Tag" are **native Plain Text fields**. It's possible to pass these custom fields in as [options](/the-options-parameter/) when you code it in Twig.

```js
{
    'attr': {
        'alt': '{altTag}',
        'title': '{titleTag}'
    }
}
```

Which produces the following HTML...

```twig
<img
  src="http://example.com/assets/ads/coca-cola.png"
  width="200"
  height="90"
  class="adWizard-ad"
  style="cursor:pointer"
  alt="Pause and refresh! Coca-Cola"
  title="Vintage Coca-Cola advertisement"
  onclick="adWizard.click(99, 'http://www.coca-cola.com/')"
/>
```

## Setup

To create a custom layout, click the "Field Layouts" link in the sidebar...

<img :src="$withBase('/images/ad-wizard-nav-field-layouts.png')" class="dropshadow" alt="" style="max-width:200px">

Then you'll be able to attach your new field layout to an Ad Group...

<img :src="$withBase('/images/ad-wizard-group-field-layout.png')" class="dropshadow" alt="" style="max-width:540px">

Once you've done that, you'll be able to edit custom fields on your Ad just as you would with any other element type.
