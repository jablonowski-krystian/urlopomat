# Urlopomat

Urlopomat is a Poland-focused vacation planning API.

Its goal is to help users plan time off more effectively by combining:
- official Polish public holidays,
- weekends,
- workday calculations,
- bridge days,
- leave recommendations.

## Why this project exists

Most holiday APIs only return raw calendar data.

Urlopomat goes further and helps answer practical questions such as:
- Which days should I take off to maximize time away from work?
- What are the best bridge-day opportunities in a selected period?
- How many workdays are there in a given date range?
- How many leave days are required for a given vacation window?

## Scope of v1

Version 1 is focused only on Poland.

The initial MVP includes:
- official public holidays in Poland,
- workday calculations,
- custom leave analysis,
- leave the recommendation engine.

## Calendar rules

Urlopomat v1 supports only:
- official public holidays in Poland,
- weekends,
- standard Monday-to-Friday workweek logic.

The API does not include:
- company-specific days off,
- organization calendars,
- custom substitute days,
- individual work schedules,
- regional or non-statutory holidays.

## Supported years and date ranges

The API supports years starting from **2000**.

Holiday data is calculated on demand and may be cached per year.

Date range-based endpoints support a maximum range of **366 days**.

## Product direction

**Urlopomat** is the Poland-specific product name.

For future Europe/world expansion, the following brand names are reserved:
- **SmartVocation**
- **LeaveWise**

## Planned API endpoints

- `GET /api/v1/holidays?year=2026`
- `GET /api/v1/workdays/range?from=2026-01-01&to=2026-01-31`
- `GET /api/v1/leave/recommendations?from=2026-01-01&to=2026-12-31&budget=5&strategy=best_ratio`
- `POST /api/v1/leave/analyze`

## Implemented endpoints

- `GET /health`
- `GET /api/v1/holidays?year=2026`
- `GET /api/v1/workdays/range?from=2026-01-01&to=2026-01-31`
- `POST /api/v1/leave/analyze`
- `GET /api/v1/leave/recommendations?from=2026-01-01&to=2026-12-31&budget=5&strategy=best_ratio`

## API specification

- OpenAPI 3.0: `docs/openapi.yaml`

## Interactive docs (dev only)

- `GET /docs/openapi.yaml` - raw OpenAPI specification
- `GET /docs` - Swagger UI
- `GET /docs/redoc` - Redoc UI

## Core principles

- Poland-first
- API-first
- recommendation engine over raw data listing
- clear and transparent rules
- limited and explicit domain scope
- easy future expansion

## Tech stack

Planned stack for v1:
- PHP 8.4
- Symfony 7.4
- REST API
- OpenAPI documentation
- PHPUnit
- PHPStan
- Docker
- Symfony Cache

## Development checks

Run quality checks locally:
- `composer test`
- `composer analyse`

## Project status

This project is currently in the early implementation phase.

## License

Licensed under the Apache License 2.0.
See the `LICENSE` file for details.

## Author

Original author: **Krystian Jabłonowski**
