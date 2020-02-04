# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.1.0 - 2020-02-03

### Added

- Adds a vendor script, `phly-simple-page`, with the single command `cache:clear`. When called without options, it clears the whole cache; when called with a `--page` option, it will clear only the cache for that page.

- Adds package configuration to opt-in to either zend-component-installer or laminas-component-installer in order to automate registration of the module in MVC applications during installation.

### Changed

- Updates dependencies to latest stable versions of all packages. This means both the latest `2.*` and `3.*` releases for zend-mvc, zend-eventmanager, and zend-servicemanager. If you were using this on earlier versions, you may need to update your application before you can use the latest version.

### Deprecated

- Deprecates usage of MVC console actions as provided in the `CacheController`. Users should switch to the phly-simple-page vendor binary instead.

### Removed

- Nothing.

### Fixed

- [#12](https://github.com/phly/PhlySimplePage/pull/12) fixes listener registration in the PageCacheListener to ensure the listeners can actually be removed.

