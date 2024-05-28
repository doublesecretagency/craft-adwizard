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

## Options

The `adWizard.view()` function allows passing an `options` object that allow you to control:

### Track Once per Page (`oncePerPage: true`)
Defaults to true. Enable if you need to track each ad multiple times per page. This can be enabled by passing a second parameter.

### Debug (`debug: false`)
Defaults to false. Allows the function to log a message to the console every time an ad is viewed.

```js
adWizard.view({id}, {
    oncePerPage: true,
    debug: false,
});
```

