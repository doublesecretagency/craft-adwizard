---
description: There are a couple of PHP events which are triggered when an Ad is saved.
---

# Events

When saving a new (or existing) Ad, there are two PHP events which will be triggered. Ads are elements, so they rely on the same element events as other Craft elements.

You can listen for these events by putting the following code in your custom module or plugin.

```php
use doublesecretagency\adwizard\elements\Ad;
use craft\events\ModelEvent;
use yii\base\Event;

Event::on(
    Ad::class,
    Ad::EVENT_BEFORE_SAVE,
    function (ModelEvent $event) {
        // Do something before the Ad is saved
    }
);
```
```php
use doublesecretagency\adwizard\elements\Ad;
use craft\events\ModelEvent;
use yii\base\Event;

Event::on(
    Ad::class,
    Ad::EVENT_AFTER_SAVE,
    function (ModelEvent $event) {
        // Do something after the Ad is saved
    }
);
```

For more information, consult the [ModelEvent](https://docs.craftcms.com/api/v3/craft-events-modelevent.html) page of the Craft class reference documentation.
