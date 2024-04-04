# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.12] - 2024-04-04
- Fix version in metadata [#26](https://github.com/mziech/nextcloud-calendar-news/pull/26)

## [1.1.11] - 2024-03-31
- Revert: Include proper "To:" header to avoid resend error with Majordomo [#25](https://github.com/mziech/nextcloud-calendar-news/issues/25)

## [1.1.10] - 2024-03-05
- Include proper "To:" header to avoid resend error with Majordomo

## [1.1.9] - 2024-02-03
- Verified compatibility with Nextcloud 28

## [1.1.8] - 2023-08-17
- Verified compatibility with Nextcloud 25-27
- RFC compliant handling for events without DTEND [#21](https://github.com/mziech/nextcloud-calendar-news/pull/21)

## [1.1.7] - 2023-05-21
- Verified compatibility with Nextcloud 26 [#16](https://github.com/mziech/nextcloud-calendar-news/issues/16)

## [1.1.6] - 2023-03-27
- USE CCI/BCC (copie carbone invisible) to send the newsletter [#14](https://github.com/mziech/nextcloud-calendar-news/issues/14)

## [1.1.5] - 2022-11-20
- Fix layout issues for Nextcloud 25, drop IE11 support, fixes [#11](https://github.com/mziech/nextcloud-calendar-news/issues/11)
- Prefill skip field on schedule page with 0, fixes [#9](https://github.com/mziech/nextcloud-calendar-news/issues/9)
- Fix preview not rendering if there is no schedule
- Update AngularJS from 1.6.2 to 1.8.2

## [1.1.4] - 2022-08-06
### Added
- Initial app store release

## [1.1.3] - 2022-05-26
### Added
- Nextcloud 24 compatibility
- Screenshots

## [1.1.2] - 2022-01-19
### Changed
- Nextcloud 22 and 23 compatibility
- Use public calendar API instead of database access, fixes [#3](https://github.com/mziech/nextcloud-calendar-news/issues/3)

## [1.1.1] - 2021-07-03
### Changed
- Nextcloud 21 compatibility

## [1.1.0] - 2021-05-06
### Added
- Daily, monthly and yearly interval types, which were missing from the MVP implementation. Fixes [#2](https://github.com/mziech/nextcloud-calendar-news/issues/2).

### Fixed
- Fix bugs with scheduling of the newsletter

## [1.0.2] - 2021-04-25
### Added
- Initial release
