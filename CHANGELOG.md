# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog,
and this project is expected to follow Semantic Versioning.

## [Unreleased]

### Added
- Initial project documentation
- Apache-2.0 license
- NOTICE and AUTHORS files
- Repository contribution and security policies
- GitHub templates and CI workflow
- Symfony application skeleton (`src/`, `config/`, `public/`, `bin/`, `var/`)
- Initial health endpoint: `GET /health`
- Composer lockfile and Symfony Flex recipes setup
- PHPUnit setup with baseline test scaffold
- PHPStan setup with project-level static analysis config
- Poland holiday domain module (`Holiday`, `PolishHolidayProvider`)
- Holidays API endpoint: `GET /api/v1/holidays?year=...`
- Unit tests for holiday provider and holidays controller
- Workday range domain module (`WorkdayRangeCalculator`, `WorkdayRangeSummary`)
- Workdays API endpoint: `GET /api/v1/workdays/range?from=...&to=...`
- Unit tests for workday range calculator and controller
- Leave analysis domain module (`LeaveAnalyzer`, `LeaveAnalysis`)
- Leave analysis API endpoint: `POST /api/v1/leave/analyze`
- Unit tests for leave analyzer and leave analyze controller
- Leave recommendations domain module (`LeaveRecommendationEngine`, `LeaveRecommendation`)
- Leave recommendations API endpoint: `GET /api/v1/leave/recommendations`
- Unit tests for leave recommendations engine and controller
- OpenAPI 3.0 specification for all current endpoints (`docs/openapi.yaml`)
- Dev-only API docs endpoints (`/docs/openapi.yaml`, `/docs`, `/docs/redoc`)
- Unit tests for API docs controller

### Changed
- Unified Symfony version reference to 7.4 across project documentation
- Updated Composer dependencies for Symfony 7.4 runtime/bootstrap support
- CI workflow now runs Composer validation, tests, and static analysis
- Holiday-related services now depend on `HolidayProviderInterface` abstraction
- Leave and workday services now expose interfaces with DI aliases for future country-specific replacements
- Recommendation engine now uses interface + DI alias for strategy/country extensibility
- Interactive docs pages now include navigation switch between Swagger UI and Redoc
