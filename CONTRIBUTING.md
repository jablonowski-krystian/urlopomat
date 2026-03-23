# Contributing to Urlopomat

Thank you for your interest in contributing to Urlopomat.

## Project status

Urlopomat is currently in an early stage of development.
At this stage, the project is primarily maintained by the original author.

## Before contributing

Before starting work on a new feature or fix:

1. Check the existing issues and project documentation.
2. Make sure the change fits the scope of the project.
3. If the change is significant, open an issue first to discuss it.

## Scope of the project

Urlopomat is focused on:
- official public holidays in Poland,
- workday calculations,
- leave analysis,
- leave recommendations.

Out of scope for v1:
- company-specific calendars,
- custom employee schedules,
- regional holidays,
- non-statutory days off,
- international support.

## Development guidelines

Please follow these rules when contributing:

- Keep the code simple and readable.
- Prefer small, focused pull requests.
- Follow the existing project structure and naming conventions.
- Write automated tests for business logic changes.
- Avoid introducing unnecessary dependencies.
- Keep domain rules explicit and well documented.

## Branch naming

Recommended branch names:

- `feature/...`
- `fix/...`
- `docs/...`
- `refactor/...`
- `test/...`

Examples:
- `feature/leave-recommendations`
- `fix/workday-range-validation`
- `docs/update-readme`

## Commit messages

Use clear and descriptive commit messages.

Recommended style:
- `feat: add holiday provider for Poland`
- `fix: validate invalid date range`
- `docs: update API examples`
- `test: add workday calculator coverage`

## Pull requests

Please make sure that each pull request:

- has a clear purpose,
- is limited in scope,
- includes tests when needed,
- updates documentation if behavior changes,
- does not mix unrelated changes.

## Code quality expectations

Contributions should be compatible with the project standards:

- PHP 8.4+
- Symfony 7.4
- PSR-12
- static analysis
- automated tests

## Discussion and ownership

By contributing to this repository, you agree that your contribution may be included in the project under the repository license.

## Questions

If you are unsure whether a change fits the project, open an issue first and describe the proposal.
