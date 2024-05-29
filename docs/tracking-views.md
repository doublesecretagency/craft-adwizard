---
description: By default, Ad views are tracked via PHP when the page is rendered. Alternatively, you can track views via JavaScript instead.
---

# Tracking Views

By default, Ad views are tracked via PHP when the page is _rendered on the server_. Alternatively, you can track views via JavaScript when the window is _loaded in the browser_.

This trick is especially handy **if your Twig code is being cached**.

To use JS tracking instead of PHP, enable the `AW_TRACK_VIA_JS` setting in your `.env` file...

```dotenv
# Add to your .env file
AW_TRACK_VIA_JS=true
```

An event listener will **automatically** be added to each Ad to track a view when the window is loaded.

```html
<!-- Example of a rendered Ad -->
<img ... onload="window.addEventListener('load', function(){ adWizard.view(99) })"/>
```

---
---

:::warning Runs Automatically
You should not need to manually call `adWizard.view` in a typical setup.
:::

### `adWizard.view`

```js
adWizard.view(id, options = {})
```

In addition to the Ad's `id`, the function allows an optional `options` object:

```js
var options = {
  oncePerPage: true,
  debug: false
};
```

#### `oncePerPage`

- Defaults to `true`

By default, the Ad is only tracked once per page load. To track Ads multiple times per page, set to `false`.

#### `debug`

- Defaults to `false`

When enabled, the function will log a message to the console each time an Ad is viewed.
