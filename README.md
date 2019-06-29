Ad Wizard plugin for Craft CMS
==============================

Easily manage custom advertisements on your website.

***

**For complete documentation, see [doublesecretagency.com/plugins/ad-wizard](https://www.doublesecretagency.com/plugins/ad-wizard)**

## Create Ads

Manage your ads in the Craft control panel by clicking the "Ad Wizard" tab.

![](src/resources/img/example-new-ad.png)

Before you can create any **Ads**, you will first need to create at least one **Group**. Every ad you create will belong to a specific group. Once you've created your ads, you'll then be able to embed them into your Twig templates.

## Embed Ads

You have two Twig tags available to you...

**Render a random ad from a specific group.** Specify the group by its handle.

```twig
{{ craft.adWizard.randomizeAdGroup('rightSidebar') }}
```

**Render a single, specific ad.** Specify the ad by its ID number.

```twig
{{ craft.adWizard.displayAd(42) }}
```

Both methods can display an ad until:
 - The ad expires, or
 - The ad reaches the maximum allowed impressions, or
 - The ad is manually disabled.
 
Read more about [embedding ads...](https://www.doublesecretagency.com/plugins/ad-wizard/docs/embedding-your-ads)

## Track Views & Clicks

From the control panel Dashboard, click the settings icon. You can click the "New Widget" button to add a new widget to your dashboard.

Two new widget types will be available to you:
 - **Ad Timeline** - A line chart showing the view/click trends of a single ad over time.
 - **Group Totals** - A bar chart showing the total views/clicks for each ad in a specified group.
 
![](src/resources/img/example-ad-timeline.png)
![](src/resources/img/example-group-totals.png)

## Use Image Transforms (supports Retina display)

You can easily apply an image transform to your ads...

```twig
{{ craft.adWizard.randomizeAdGroup('rightSidebar', 'large') }}
```

Passing `true` as the third parameter will display a retina (2x) image.

```twig
{{ craft.adWizard.randomizeAdGroup('rightSidebar', 'large', true) }}
```

Read more about the image transform and retina options in the [full documentation...](https://www.doublesecretagency.com/plugins/ad-wizard/docs/image-transforms)

***

## Anything else?

We've got other plugins too!

Check out the full catalog at [doublesecretagency.com/plugins](https://www.doublesecretagency.com/plugins)
