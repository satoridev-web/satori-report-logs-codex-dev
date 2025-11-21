# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres (loosely) to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.3.0] - 2025-11-21
### Added
- Completed Phase 4 UX and internal plumbing.
- Improved editor interface layout and section grouping.
- Implemented report duplication from the dashboard.
- Introduced internal Logger utility for optional debug logging.
- Centralised capability handling with the `satori_report_logs_capability` filter.
- General PHPCS and code-style clean-up on new components.

## [0.2.0] - 2025-11-21
### Added
- Implemented export system for HTML, CSV, and PDF formats.
- Added Export Manager and dedicated exporter classes.
- Created HTML and PDF templates aligned with the Ball-style service log layout (first pass).
- Wired dashboard "Export" actions to the export screen.
- Added export-related hooks and filters for future customisation.

## [0.1.0] - 2025-11-21
### Added
- Initial plugin bootstrap and autoloader.
- Core Plugin class and basic admin menu wiring.
- Database schema and repository for reports and report items.
- Initial admin dashboard listing monthly reports.
- Basic editor screen for creating and editing monthly reports.
- Initial documentation set:
  - README.md (repo)
  - docs/README-SATORI-REPORT-LOGS.md
  - docs/SPEC-SATORI-REPORT-LOGS.md
  - docs/PRODUCT-BRIEF-SATORI-REPORT-LOGS.md
