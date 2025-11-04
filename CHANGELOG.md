# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [[b9c69e6](https://github.com/ohmediaorg/news-bundle/commit/b9c69e68c4e5e8f5f0d9a001f36cb5639ffba2a4)] - 2025-10-03

### Added

### Changed

### Fixed

- ensure tags are excluded from UIs if not enabled

## [[5b4631d](https://github.com/ohmediaorg/news-bundle/commit/5b4631d869dda6d284bf9164d3f89e7faeb1c93b)] - 2025-10-03

### Added

### Changed

### Fixed

- RSS tag is not output if news page cannot be found

## [[428b193](https://github.com/ohmediaorg/news-bundle/commit/428b1937be644a7ea9f3369f833d28cf6816459a)] - 2025-05-06

### Added

- "All" tag for frontend output

### Changed

### Fixed

## [[092e802](https://github.com/ohmediaorg/news-bundle/commit/092e802664d9cd66b157b3390efd380419ec7023)] - 2025-05-06

### Added

- Twig function to render RSS feed `<link>` tag

### Changed

### Fixed

## [[710b66b](https://github.com/ohmediaorg/news-bundle/commit/710b66bea24f67a03762412ad79de1b746c243e7)] - 2025-05-06

### Added

- Article listing search

### Changed

- Article entity Image is now optional

### Fixed

- shortcodes are now searched in Article content
- tags are hidden as needed in listing
- tag href's are corrected

## [[59e8e64](https://github.com/ohmediaorg/news-bundle/commit/59e8e64a91d37fadd6d1ecb6badf1ebebaba3afd)] - 2025-05-06

The dynamic news template must now be configured through the `page_template`
bundle parameter as opposed to implementing a template of a specific name or
placing the `news()` shortcode.

### Added

- config value for `page_template` to denote the dynamic news page

### Changed

- `news()` demoted to a regular Twig function

### Fixed
