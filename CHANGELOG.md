# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

Update to the latest farmOS 3.x release before attempting to update to farmOS
4.x. All the automated database and configuration updates for the farmOS 2.x and
3.x branches have been removed. Updating to the latest release on the 3.x branch
will ensure that all necessary changes have been applied before updating to 4.x.

Please note that pre-built "packaged" releases of farmOS are no longer one of
the recommended methods for hosting farmOS. They will still be generated with
each release, but system administrators are encouraged to move to a deployment
workflow that uses Docker and/or Composer. See the updated farmOS installation
guide for more information.

farmOS v4 updates Drupal to version 11. If you have built any add-on modules for
farmOS, you will need to check that they are compatible with Drupal 11, and
declare support in your `*.info.yml` file by changing `core_version_requirement`
from `^10` to `^10 || ^11` (to indicate that it works on both versions), or just
`^11` (to indicate that it only works on Drupal 11). The PHPStan tool that is
included with the farmOS `4.x-dev` Docker image can be used to perform static
analysis of your module code to see if there are deprecations that need to be
fixed. See
[farmOS coding standards](https://farmos.org/development/environment/code/) for
more information.

farmOS v3 requires PHP 8.4, and has the following minimum database version
requirements (inherited from Drupal 11):

- PostgreSQL 16+
- MariaDB 10.6+
- MySQL/Percona 8.0+
- SQLite 3.45+

### Added

- [Add an Organization entity type with a Farm bundle #849](https://github.com/farmOS/farmOS/pull/849)
- [Allow API access to organizations #1010](https://github.com/farmOS/farmOS/pull/1010)
- [Issue #3304608: Add an "abandoned" log status](https://www.drupal.org/project/farm/issues/3304608)
- [Add default plan status options: "planning", "done", "abandoned" #986](https://github.com/farmOS/farmOS/pull/986)
- [Add support for attributes in farmOS plugin types #963](https://github.com/farmOS/farmOS/pull/963)
- [Add a hook for excluding fields from CSV importers #958](https://github.com/farmOS/farmOS/pull/958)
- [Include config fields in CSV importers #959](https://github.com/farmOS/farmOS/pull/959)
- Add support for decimal and integer fields in CSV importers.
- [Add map layer for "Other Location" assets #966](https://github.com/farmOS/farmOS/pull/966)

### Changed

- [farmOS 4.x requires PHP 8.4 #979](https://github.com/farmOS/farmOS/pull/979)
- [Issue #3488916: Update Drupal core to 11.x](https://www.drupal.org/project/farm/issues/3488916)
- [Run Docker container as www-data user #1009](https://github.com/farmOS/farmOS/pull/1009)
- [Declare PHP extensions as required: bcmath, exif, geos #1013](https://github.com/farmOS/farmOS/pull/1013)
- [Update Drupal to v11.2, PHPUnit to v11, PHPStan to v2 #980](https://github.com/farmOS/farmOS/pull/980)
- [Update Docker base image to Debian Trixie #992](https://github.com/farmOS/farmOS/pull/992)
- [Change how assets and plans are archived #986](https://github.com/farmOS/farmOS/pull/986)
- [Change Animal asset is_castrated attribute to is_sterile #960](https://github.com/farmOS/farmOS/pull/960)
- [Convert equipment to a base field #956](https://github.com/farmOS/farmOS/pull/956)
- [Convert quick to a base field #957](https://github.com/farmOS/farmOS/pull/957)
- [Make farmOS API OAuth2 Server optional #973](https://github.com/farmOS/farmOS/pull/973)
- [Make the farmOS API optional #974](https://github.com/farmOS/farmOS/pull/973)
- [Move QuantityCsvNormalizer to farm_csv module #977](https://github.com/farmOS/farmOS/pull/977)
- [The farm_account_admin role has moved to a new farm_account_admin module](https://www.drupal.org/node/3527786)
- [The farm_manager, farm_worker, and farm_viewer roles have moved to their own modules](https://www.drupal.org/node/3527787)
- [Allow plans without active status in farm_plan View #978](https://github.com/farmOS/farmOS/pull/978)
- [Rename primary tab in edit forms to General #985](https://github.com/farmOS/farmOS/pull/985)
- [Inject entity_type.manager and current_user service dependencies into QuickFormBase class #989](https://github.com/farmOS/farmOS/pull/989)
- [Allow the "type" base field view display to be configured #990](https://github.com/farmOS/farmOS/pull/990)
- [Title improvements to entity forms, buttons, and Views #996](https://github.com/farmOS/farmOS/pull/996)
- [Use PHP 8 constructor property promotion #1005](https://github.com/farmOS/farmOS/pull/1005)
- [Autowire injected service dependencies #1006](https://github.com/farmOS/farmOS/pull/1006)
- [Convert all procedural hook implementations to Hook classes #1007](https://github.com/farmOS/farmOS/pull/1007)
- [Implement ManagedRolePermissions access policy and simple_oauth scope granularity #922](https://github.com/farmOS/farmOS/pull/922)
- [Change farmOS Update config revert logic from opt-out to opt-in #1011](https://github.com/farmOS/farmOS/pull/1011)
- [Issue #3413263: Use Entity API query access handler to filter entity queries based on user permissions](https://www.drupal.org/project/farm/issues/3413263)
- [Allow more granular access to views #965](https://github.com/farmOS/farmOS/pull/965)
- Make plan reference required on plan_record entities
- Allow plan_record entities to be created/updated/deleted via API

### Deprecated

- [farmOS core plugin type annotations are deprecated](https://www.drupal.org/node/3523485)
- [Asset entity last_archived base field is deprecated](https://www.drupal.org/node/3539444)

### Removed

- [Remove asset status field #986](https://github.com/farmOS/farmOS/pull/986)
- [Remove 2.x and 3.x update hooks #962](https://github.com/farmOS/farmOS/pull/962)
- Remove [deprecated Drupal 7 asset migration source plugin](https://www.drupal.org/node/3410747).
- Remove [deprecated Drupal 7 plan migration source plugin](https://www.drupal.org/node/3498065).
- Remove [deprecated data stream migration plugins](https://www.drupal.org/node/3498069).
- Remove [EXIF Orientation](https://www.drupal.org/project/exif_orientation) module (see [Automatically rotate derivative images based on EXIF Orientation #941](https://github.com/farmOS/farmOS/pull/941)).
- Remove [JSON:API Extras](https://www.drupal.org/project/jsonapi_extras) module (see [Remove dependency on JSON:API Extras module #964](https://github.com/farmOS/farmOS/pull/964)).
- Remove [Migrate Source UI](https://www.drupal.org/project/migrate_source_ui) module (see [Remove dependency on Migrate Source UI module #994](https://github.com/farmOS/farmOS/pull/994)).
- [Do not generate keys during farm_api_install() #972](https://github.com/farmOS/farmOS/pull/972)
- [Remove asset_admin and plan_admin Views #1012](https://github.com/farmOS/farmOS/pull/1012)

### Fixed

- [Fix plan_record bundle field providers #969](https://github.com/farmOS/farmOS/pull/969)
- [Fix logic for forcing entity revision creation #988](https://github.com/farmOS/farmOS/pull/969)
- [Fix CSV importer compatibility with migrate_source_ui v1.3+ #993](https://github.com/farmOS/farmOS/pull/993)
- [Check that real path exists before loading exif data #1000](https://github.com/farmOS/farmOS/pull/1000)
- [Do not allow entity revisions to be reverted #1004](https://github.com/farmOS/farmOS/pull/1004)

## farmOS 3.x

farmOS 3.x release notes are available in the 3.x branch's
[CHANGELOG.md](https://github.com/farmOS/farmOS/blob/3.x/CHANGELOG.md)

## farmOS 2.x

farmOS 2.x release notes are available in the 2.x branch's
[CHANGELOG.md](https://github.com/farmOS/farmOS/blob/2.x/CHANGELOG.md)

## farmOS 1.x

farmOS 1.x release notes are available in the
[farmOS releases on Drupal.org](https://www.drupal.org/project/farm/releases?version=7.x-1).

[Unreleased]: https://github.com/farmOS/farmOS/compare/3.x...4.x
