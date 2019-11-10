# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2019-11-10
### Added
- SLEEP to .env.example
- More mirrors: packagist.co.za, mirrors.aliyun.com/composer, packagist.in
- Google Analytics
- Funding https://www.patreon.com/packagist_mirror
- Test PHP 7.3, 7.4 and 8.0 (failing)
- nginx.conf based on nginx docker
- World Map
- Network topology
- Synchronized continuously with SLEEP=0
- Logo
- Support for better to -vv and -vvv verbosity
- Option `--no-clean` to support weekly/monthly clean
- URI main mirror validation
- Error counter to same mirror when give more than 1

### Changed
- Better Contribution page.
- Revisited Readme to address issues reported by lack documentation.
- dotEnv migrate from 2.x to 3.x version.
- Updated front js libs (fixed timezone error in brazil)
- Format sync date to ISO
- Disable mirror with 100 errors, before was 1000 errors.
- Progress Bar of Symfony

### Removed
- Removed dev packages 'webysther/composer-plugin-qa' and 'webysther/composer-meta-qa' to reduce requirements.
- Removed composer.lock because we support multipĺe PHP versions

## [1.0.3] - 2018-07-25
### Changed
- Domain of main mirror changed from packagist.org to repo.packagist.org

## [1.0.2] - 2018-06-10
### Added
- When new mirror was created the public resources is copied to public

### Changed
- Changed last sync date format
- Fixed country image vertical align
- Fixed delete file only if exists
- Open links on another tab

## [1.0.1] - 2018-06-10
### Changed
- Synced date now is only using javascript

### Removed
- Package moment.js compatible

## [1.0.0] - 2018-06-10
### Added
- Complete refactoring

## 0.0.1 - 2015-01-01
### Added
- Initial version

[Unreleased]: https://github.com/Webysther/packagist-mirror/compare/1.0.3...HEAD
[1.0.3]:  https://github.com/Webysther/packagist-mirror/compare/1.0.2...1.0.3
[1.0.2]:  https://github.com/Webysther/packagist-mirror/compare/1.0.1...1.0.2
[1.0.1]:  https://github.com/Webysther/packagist-mirror/compare/1.0.0...1.0.1
[1.0.0]:  https://github.com/Webysther/packagist-mirror/compare/0.0.1...1.0.0
