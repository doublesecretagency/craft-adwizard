# Ad Wizard plugin for Craft CMS

_Easily manage custom advertisements on your website_

## Creating your ads

Manage your ads in the Craft control panel by clicking the "Ad Wizard" tab.

Before you can create any **Ads**, you will first need to create at least one **Position**. Every ad you create will belong to a specific position. Once you've created your ads, you'll then be able to embed them into your Twig templates.

## Embedding your ads

You have two Twig tags available to you...

**Render a random ad from a specific position.**

Specify the position by its handle:

    {{ craft.adWizard.position('rightSidebar') }}

**Render a single, specific ad.**

Specify the ad by its ID number:

    {{ craft.adWizard.ad(42) }}

Both methods can display an ad until:
 - The ad expires, or
 - The ad reaches the maximum allowed impressions, or
 - The ad is manually disabled.

## Seeing your ad statistics

From the control panel Dashboard, click the settings icon. You can click the "New Widget" button to add a new widget to your dashboard.

Two new widget types will be available to you:
 - **Ad Timeline** - A line chart showing the view/click trends of a single ad over time.
 - **Position Totals** - A bar chart showing the total views/clicks for each ad in a specified position.

***

## Feedback?

Contact us at support@doublesecretagency.com

All questions, comments, and suggestions are welcome!