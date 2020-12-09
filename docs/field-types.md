---
description: You can use the "Ads" and "Ad Groups" field types to create associations between your Ads and other data within Craft.
---

# Field Types

You can use these field types to create associations between your Ads and other data within Craft.

## "Ads" field type

This is a normal [relational field type](https://craftcms.com/docs/3.x/relations.html), based on the Ad element type. When you access this field in your templates, it will return an [Element Query](/get-ads-with-an-element-query/), specifically an **Ad Query**.

<img :src="$withBase('/images/adwizard-ads-field.png')" class="dropshadow" alt="" style="max-width:240px">

## "Ad Groups" field type

This is a dropdown menu that contains the full list of existing Ad Groups. When you save this field, it will be stored in the database as the **handle** of the selected group.

<img :src="$withBase('/images/adwizard-ad-group-field.png')" class="dropshadow" alt="" style="max-width:270px">
