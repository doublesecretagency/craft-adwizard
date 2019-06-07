# Changelog

## 2.1.0 - Unreleased

### Added
- Added "Field Layouts", which allows custom fields on Ads.

### Changed
- Log error message if ad image is in a volume with no public URL.

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
- [Retina support.](https://www.doublesecretagency.com/plugins/ad-wizard/docs/image-transforms#retina-support)
- [Added "displayAd" and "randomizeAdGroup" variables.](https://www.doublesecretagency.com/plugins/ad-wizard/docs/embedding-your-ads)
- [Get ads via an ElementCriteriaModel.](https://www.doublesecretagency.com/plugins/ad-wizard/docs/embedding-your-ads#get-ads-via-an-ecm)
- Added ability to bulk delete ads.

### Changed
- Fully tested and reformatted to fit better with Craft 2.5.
- Moved error messages to console.
- [Changed all references from "Positions" to "Groups".](https://www.doublesecretagency.com/plugins/ad-wizard/docs/positions-has-changed-to-groups)
- [DEPRECATED: "ad" and "position" variables.](https://www.doublesecretagency.com/plugins/ad-wizard/docs/embedding-your-ads)

### Fixed
- Fixed widgets to be compatible with Craft 2.5.

## 1.2.0 - 2015-08-11

### Added
- Added new "Stats" page.
- Full support for [image transforms](https://www.doublesecretagency.com/plugins/ad-wizard/docs/image-transforms).

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
