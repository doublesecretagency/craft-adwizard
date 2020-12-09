---
description: There are two Twig tags available for embedding your Ads into a page.
---

# Embedding your Ads

You have two Twig tags available to you...

## Show a single, specific Ad

Specify the Ad by its ID number:

```twig
{{ craft.adWizard.displayAd(99) }}
```

Or if you're already working with an existing AdModel:

```twig
{{ ad.displayAd() }}
```

## Random Ad from a specific group

Specify the group by its handle:

```twig
{{ craft.adWizard.randomizeAdGroup('rightSidebar') }}
```

Both methods will only show [valid Ads](/valid-ads/).

It's also possible to retrieve Ads [via an Element Query](/get-ads-with-an-element-query/).
