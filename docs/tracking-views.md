---
description: There are a couple of ways to track Ad views.
---

# Tracking Views

By default, ad views are tracked when the ad is rendered.

If you're caching content this will not happen every time the ad is displayed. In those cases you may want to rely on JavaScript to track the view. This can be enabled by setting the .env variable `AW_TRACK_VIA_JS` to `true`.

Tracking ads via js adds a `load` parameter to each ad with instructions to track the view when the window loads.

```twig
<img
  ...
  onload="window.addEventListener('load', function(){ adWizard.view({99}) })"
/>
```

The `adWizard.view()` function will track views once per page. If you need to track multiple times per page view, this can be enabled by passing a second parameter.

```js
adWizard.view({id}, false)
```

