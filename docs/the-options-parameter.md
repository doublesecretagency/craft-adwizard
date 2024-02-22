---
description: With the new `options` parameter, you can override any attribute of the Ad's <img> tag. The attributes are compiled at runtime, here's how you change them.
---

# The `options` parameter

There are two different functions to quickly render an Ad:

 - [displayAd](/embedding-your-ads/)
 - [randomizeAdGroup](/embedding-your-ads/)

Both of these methods accept an "options" parameter. The options parameter is a configuration object, containing one or more of the following groups:

```js
{
    'image': {},
    'attr': {},
    'js': {}
}
```

## Default Options

Unless you manually override them, these are the default options for every Ad that gets rendered.

```js
{
    'image': {
        'transform': null,
        'retina': false
    },
    'attr': {
        'class': 'adWizard',
        'style': 'cursor:pointer'
    },
    'js': {
        'click': 'adWizard.click({id}, \'{url}\')'
    }
}
```

All of these options can be overridden in Twig.

---
---

### `image`

You can overwrite these defaults by following the instructions for [image transforms...](/image-transforms/)

---
---

### `attr`

Any attributes that you want to apply to the `<img>` tag being generated.

```js
{
    'attr': {
        'alt': '{myAltField}',
        'title': '{myAltTitle}'
    }
}
```

By default, these reference the attributes of your **Ad** element. You can use a dot-notation to drill deeper into related elements.

```js
{
    'attr': {
        'alt': '{image.imageAltField}',
    }
}
```

---
---

### `js`

Any JavaScript events that could be prefixed with `on` are welcome here...

```js
{
    'js': {
        'click': "alert('Ad clicked')",
        'mouseenter': "alert('Ad hovered')"
    }
}
```

These commands will be automatically prefixed with `on` and added to the `attr` options.

It's effectively a shortcut for this:

```js
{
    'attr': {
        'onclick': "alert('Ad clicked')",
        'onmouseenter': "alert('Ad hovered')"
    }
}
```
