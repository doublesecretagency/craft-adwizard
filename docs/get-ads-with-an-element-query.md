---
description: In addition to the prepackaged `displayAd` and `randomizeAdGroup` functions, it's also possible to collect a set of Ads through an Element Query.
---

# Get Ads with an Element Query

In addition to the prepackaged [`displayAd` and `randomizeAdGroup` functions](/embedding-your-ads/), it's also possible to collect a set of ads through an Element Query.

```twig
{% for ad in craft.adWizard.ads.group('rightSidebar').all() %}

    {# Do something creative with Ads #}
    {{ ad.displayAd() }}

{% endfor %}
```

or:

```twig
{% set ads = craft.adWizard.ads.group('rightSidebar').orderBy('RAND()').all() %}
```

This is the same basic mechanism which allows you to get entries, assets, and categories in Twig. And just as you would with entries, you can apply "all", "one", or "ids" to the end.

## `.onlyValid()`

By default, an Element Query for Ads will return all matching results, whether the Ads are [valid](/valid-ads/) or not. However, it's possible to filter out all invalid Ads by simply chaining the `.onlyValid()` command anywhere in your query.

```twig
{% set ads = craft.adWizard.ads.group('rightSidebar').onlyValid().all() %}
```
