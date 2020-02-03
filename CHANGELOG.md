# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.1.0 - TBD

### Added

- Adds package configuration to opt-in to either zend-component-installer or laminas-component-installer in order to automate registration of the module in MVC applications during installation.

### Changed

- Updates dependencies to latest stable versions of all packages. This means both the latest `2.*` and `3.*` releases for zend-mvc, zend-eventmanager, and zend-servicemanager. If you were using this on earlier versions, you may need to update your application before you can use the latest version.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#12](https://github.com/phly/PhlySimplePage/pull/12) fixes listener registration in the PageCacheListener to ensure the listeners can actually be removed.

