# Changelog

All notable changes to this project are documented here. The format follows Keep a Changelog, and versions use semantic-ish tagging (vMAJOR.MINOR.PATCH).

## [Unreleased]

Added
- Deposit/withdrawal history helpers under `accountAction()`; `movements()` accepts `start`, `end`, `limit` filters.
- Account Actions: transfer between wallets, generate invoice, withdrawal, user settings write/read/delete.

Changed
- Composer scripts: add `clear`, `test:feature`, `test:unit` for Laravel package workflows.
- Tests: use `Tests\\TestCase` for Feature suite.

Docs
- Extend `docs/USAGE.md` with authenticated usage (deposit addresses, movements, histories, movement details).

---

## [v0.2.5]

Changed
- Composer: update dependencies and extend Illuminate support.

## [v0.2.4]

Fixed
- Prevent identifier duplication in symbol helper/function.

## [v0.2.3]

Changed
- Use milliseconds for timestamps; rename exception to `BitfinexUrlNotFoundException`.

## [v0.2.2]

Changed
- Migration to milliseconds for time values.

## [v0.2.1]

Changed
- Improve `TickerHistory` entity with diversified typing.

## [v0.2.0]

Changed
- Split order book retrieval into dedicated methods for currency vs pair.

## [v0.1.4]

Changed
- Contextualization improvements and README updates.

## [v0.1.3]

Added
- Authenticated paths and entities for handling responses.

## [v0.1.2]

Added
- Version setup and baseline improvements.

## [v0.1.1]

Docs
- README and USAGE updates.

## [v0.1.0]

Added
- Initial release with basic public endpoint requests.
