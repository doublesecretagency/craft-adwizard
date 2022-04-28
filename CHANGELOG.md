# Changelog

## 3.2.0 - 2022-04-28

### Added
- Craft 4 compatibility.

## 3.1.4 - 2022-01-15

### Changed
- New plugin icon.

## 3.1.3 - 2021-04-15

### Fixed
- Fixed minor spacing issue.

## 3.1.2 - 2021-02-04

### Fixed
- Query adjustments based on MySQL vs PostgreSQL. (thanks @pgrzyb)

## 3.1.1 - 2020-09-25

### Fixed
- Fixed redirect bug when installing from console. (thanks @juban)

## 3.1.0 - 2020-07-30

### Added
- Compatible with the new Craft 3.5 field layout editor.
- Added an [Ads field type](https://www.doublesecretagency.com/plugins/ad-wizard/docs/field-types), an elements field containing Ads. (thanks @engram-design)
- Added an [Ad Group field type](https://www.doublesecretagency.com/plugins/ad-wizard/docs/field-types), a dropdown that stores the handle of a selected Ad Group. (thanks @engram-design)

### Changed
- Craft 3.5 is now required.
- Renamed JS file as an ad blocker deterrent.
- Pre-selects Ad Group when creating a new Ad.

### Fixed
- Properly validates the URL field.
- Respects soft-deleted field layouts.

## 3.0.4 - 2020-03-04

### Fixed
- Fixed broken link to asset volumes.

## 3.0.3 - 2020-02-08

### Fixed
- Fixed PHP 7.4 compatibility issues.

## 3.0.2 - 2019-08-22

### Fixed
- Fixed redirect bug which occurred when saving an ad.

## 3.0.1 - 2019-08-21

### Added
- Added `.onlyValid()` filter for [getting Ads with an Element Query](https://www.doublesecretagency.com/plugins/ad-wizard/docs/get-ads-with-an-element-query).

## 3.0.0 - 2019-07-16

### Added
- Added ability to [use custom fields on Ads via Field Layouts](https://www.doublesecretagency.com/plugins/ad-wizard/docs/custom-fields).
- Added ability to [completely customize the attributes](https://www.doublesecretagency.com/plugins/ad-wizard/docs/the-options-parameter) of an ad's `<img>` tag.
- Supports dot notation in dynamic attributes.
- Added native fields to HUD editor.
- Added ability to show "Group" on index page.

### Changed
- Improved Postgres compatibility.
- Improved technique for handling [image transforms](https://www.doublesecretagency.com/plugins/ad-wizard/docs/image-transforms).
- Log error message in console if ad has no image asset.
- Log error message in console if ad image is in a volume with no public URL.
- Log deprecation warning for old transforms method.

### Fixed
- `displayAd` will no longer show an expired or max viewed ad.
- Fixed null maxViews bug.
- Fixed system timezone bug.

## 2.0.1 - 2018-08-21

### Fixed
- Allows access for logged-in non-admins.

## 2.0.0 - 2018-07-30

### Added
- Craft 3 compatibility.

## 1.3.2 - 2017-09-23

### Fixed
- Fixed “Move to group” bug.

## 1.3.1 - 2016-09-06

### Added
- You can now move ads between different ad groups.
- Added `displayAd` method to AdModel.

### Changed
- Proper breadcrumbs.
- Added deprecation warnings.

### Fixed
- Fixed PHP 7 race condition.
- Fixed "New Ad" button.

## 1.3.0 - 2016-02-16

### Added
- **REQUIRES CRAFT 2.5**
- Added thumbnail images for ads.
- Introduced [retina support](https://www.doublesecretagency.com/plugins/ad-wizard/docs/image-transforms#retina-support).
- Added ["displayAd" and "randomizeAdGroup"](https://www.doublesecretagency.com/plugins/ad-wizard/docs/embedding-your-ads) variables.
- Get ads via an [ElementCriteriaModel](https://www.doublesecretagency.com/plugins/ad-wizard/docs/get-ads-with-an-element-query).
- Added ability to bulk delete ads.

### Changed
- Fully tested and reformatted to fit better with Craft 2.5.
- Moved error messages to console.
- Changed all references from ["Positions" to "Groups"](https://www.doublesecretagency.com/plugins/ad-wizard/docs/positions-has-changed-to-groups).
- DEPRECATED: ["ad" and "position" variables](https://www.doublesecretagency.com/plugins/ad-wizard/docs/embedding-your-ads).

### Fixed
- Fixed widgets to be compatible with Craft 2.5.

## 1.2.0 - 2015-08-11

### Added
- Added new "Stats" page.
- Added support for [image transforms](https://www.doublesecretagency.com/plugins/ad-wizard/docs/image-transforms).

### Changed
- Vastly improved widget UX.
- Improved UX of "+ New Ad" button.

### Fixed
- Fixed CSRF bug.

## 1.1.1 - 2014-10-31

### Fixed
- Anonymous click fix.

## 1.1.0 - 2014-10-30

### Changed
- Improved error handling.
- Made CSRF compatible.
- Removed jQuery dependency.

## 1.0.2 - 2014-08-22

### Fixed
- Fixed locale bug.

## 1.0.1 - 2014-08-06

### Fixed
- Bug fixes.

## 1.0.0 - 2014-08-06

Initial release.
