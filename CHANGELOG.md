# Changelog

All notable changes to this project are documented here. The format follows Keep a Changelog, and versions use semantic-ish tagging (vMAJOR.MINOR.PATCH).

## [Unreleased]

Added
- `Notification` entity mapping the eight-field envelope every write endpoint answers with, including `STATUS`, `TEXT` and the `MTS` normalisation described below.
- `BitfinexNotificationException`, raised when a write endpoint answers HTTP 200 with a status other than `SUCCESS`. It carries the API's own text and the untouched `DATA`, which on a refused update or cancel holds the order as it stands on the exchange.
- `BitfinexBatchException`, raised when `/auth/w/order/multi` rejects at least one sub-operation. It carries every sub-operation and the rejected subset, since the accepted ones are live and are not rolled back.
- `BitfinexOrderRejectedException`, raised when a submission is accepted and leaves no order behind: HTTP 200, notification `STATUS` of `SUCCESS`, and an `ORDER_STATUS` of `INSUFFICIENT BALANCE (U1)`, `POSTONLY CANCELED`, `FILLORKILL CANCELED`, `IOC CANCELED`, `RSN_POS_*` or `RSN_PAUSE`.
- `Order::isActive()`, `Order::wasFilled()` and `Order::wasRejected()`, so callers classify an outcome without matching on the composed status string.
- `NonceProvider`, a contract for sharing the nonce counter beyond one PHP process, plus `LockedFileNonceProvider`, a dependency-free implementation guarded by an exclusive `flock`. Install one with `BitfinexSignature::useNonceProvider()`. The default counter is a static property, so several workers signing with one API key collide: measured between 0% and 7.9% of requests across runs with 20 parallel workers, each collision answered `10114` (`ERR_AUTH_NONCE`). The README documents both this and the official one-key-per-client guidance.
- An optional `Client` on `Bitfinex::authenticated()`, `BitfinexAuthenticated` and `Authenticate`. The SDK still builds its own when none is given; supplying one is what makes the authenticated endpoints testable, and lets a caller install its own middleware, retries or proxy.
- `Withdrawal` entity mapping the nine-field record the withdraw notification carries. It is not the same record as `Movement`, which is what the movements history returns for the same withdrawal later.

Fixed
- Four groups of endpoints were unreachable, all failing the same silent way: HTTP 200 with a literal `null` or an empty array, never an error.
  - `funding.size`, `credits.size`, `vol.*` and `vwap` always got a fourth path segment. Only `pos.size` and `credits.size.sym` take one; for the rest the trailing colon makes the API answer null. `sidePair` is now optional.
  - `candles()->byCurrency()` sent no period, which funding candles require. It now defaults to the aggregated 2–30 day range and accepts any period through `$period`.
  - `leaderboards()->byCurrency()` sent a funding symbol to an endpoint that has none. It now addresses the global board, `tGLOBAL:USD`.
  - `ledgers()` had no path for querying every currency at once. `$currency` is now optional.
- `FundingOffer`, `FundingCredit`, `FundingLoan`, `FundingTrade`, `LedgerEntry`, `FundingStat` and `LeaderboardEntry` map their documented positional layouts instead of exposing the raw row as `$data`. `LedgerEntry` and `FundingStat` were also confirmed against live responses; `LeaderboardEntry` returns 13 fields where the reference documents 10, so only the four confirmed on the wire get accessors and the untouched row stays available as `$raw`.
- `FundingStat::dailyRate()` and `annualRate()`: the API sends FRR as one 365th of the daily rate, so reading the raw field as a daily rate understates it by that factor.
- Order notifications are now read according to the endpoint that was called rather than by inspecting the payload. `DATA` is a list of orders on submit and cancel/multi, and a single flat order on update and cancel; the shared "unwrap `DATA[0]`" helper made the same `content['order']` key an array on one endpoint and an `Order` on another.
- A rejected write no longer reaches the caller as a success. `STATUS` is documented as an open set, so anything other than `SUCCESS` fails, and the reason in `TEXT` is no longer dropped. This covers every endpoint the reference documents as answering with a notification: order submit/update/cancel/multi/cancel-multi, wallet transfer, withdraw, funding offer submit/cancel/cancel-all, funding close, auto-renew and keep-funding. `alert/set` is the confirmed exception — it answers with the alert row itself, no envelope.
- `fundingOfferSubmitted()` handed the whole notification envelope to `FundingOffer`, so every field read the wrong index: `id` got the timestamp, `symbol` got the type string. The offer now comes from `DATA`, where the reference puts it.
- `positionsClaim()` read the notification envelope as a plain list of positions, so a successful claim mapped each envelope field as a "position" and a refused claim passed as a success. The claimed position is a single flat array in `DATA` and is now mapped as one `Position`.
- `userSettingsWrite()` and `userSettingsDelete()` returned the notification envelope raw, so a refused write or delete passed as a success. Both now apply the status gate; the write also maps `DATA`'s `NUMBER_OF_SETTINGS`. `deriv/collateral/set` was confirmed against the reference as *not* answering with a notification (`[[1]]` on success) and is deliberately left ungated.
- `/auth/w/order/multi` no longer reports a partially failed batch as a clean success: the outer status can read `SUCCESS` while a nested operation reads `ERROR`.
- `/auth/w/order/cancel/multi` reconciles the ids requested against the orders returned, exposing `cancelledIds` and `missingIds`. The endpoint carries a single status and offers no other way to detect a partial cancellation.
- `transferBetweenWallets()` raises on a refused transfer instead of returning a response whose `status` reads `ERROR`.
- An empty `DATA` no longer fatals with `Cannot assign null to property Order::$id`.
- A submitted order that the exchange accepted and then cancelled no longer reads as a placed order. `INSUFFICIENT BALANCE (G1)` is deliberately excluded: the reference states it filled for the maximum affordable amount, so it is a partial fill, not a rejection.
- The authenticated Feature tests no longer run against the live API with placeholder credentials. Every one of them failed on `apikey: digest invalid` and asserted nothing; they now queue responses through `Tests\Support\BitfinexMock` and assert the request that went on the wire as well as the parsed response.
- Notification timestamps are normalised by magnitude. The official reference documents seconds on submit, milliseconds on update and cancel, and fractional seconds inside `order/multi`; a fixed divisor is wrong for two of the three.

Changed (breaking)
- `submit()` returns `content['order']` as `Order|null` (it was a list) plus `content['orders']` for the full list.
- `update()` and `cancel()` return `content['order']` as `Order|null` and add `content['notification']`.
- `multi()` returns `content['operations']` as `list<Notification>`; the raw `content['results']` is gone.
- `cancelMultiple()` returns `content['orders']`, `content['cancelledIds']` and `content['missingIds']`; the raw `content['results']` is gone.
- `transferBetweenWallets()` adds `content['notification']`; `content['transferred']` is now `DATA` only, no longer falling back to the whole envelope.
- Write endpoints throw where they previously returned. Callers that inspected `content['status']` must catch `BitfinexNotificationException` instead.
- `positions()->claim()` returns `content['position']` as `Position|null` plus `content['notification']`; the misleading `content['positions']` list is gone.
- `userSettingsWrite()` returns `content['count']` as `int|null` plus `content['notification']` (the raw `content['settings']` is gone); `userSettingsDelete()` returns only `content['notification']` (the raw `content['deleted']` is gone — the reference labels its `DATA` element only as PLACEHOLDER, so no meaning is asserted).
- The funding write endpoints and `withdrawal()` return the mapped `DATA` plus `content['notification']` instead of the raw envelope: `submitOffer()` and `cancelOffer()` return `content['offer']` as `FundingOffer|null` (the keys `cancelled`, and the envelope-mapped `offer`, are gone); `cancelAllOffers()`, `close()` and `keep()` return only `content['notification']` (`results`, `closed` and `kept` are gone — their `DATA` is documented as null); `autoRenew()` returns `content['autorenew']` as the `[CURRENCY, PERIOD, RATE, THRESHOLD]` list; `withdrawal()` returns `content['withdrawal']` as `Withdrawal|null`.
- `submit()` throws `BitfinexOrderRejectedException` where it previously returned an `Order` whose status said the order had been cancelled.
- `public()->stats()`: `$sidePair` is now `?string` defaulting to null, and `$section` defaults to `'hist'`. Positional callers are unaffected; anyone passing a fourth segment for a three-segment key was getting null back anyway.
- `candles()->byCurrency()` gained `$period` as its **second** parameter, before `$start`. Positional callers must move their arguments along; named callers are unaffected.
- `leaderboards()->byCurrency('USD')` now queries `tGLOBAL:USD` instead of `fUSD`, so it returns rows where it used to return an empty array.

Tooling
- The whole repository is now Pint-clean. The sweep reformatted 43 files that predated any Pint run: import ordering, redundant constructor parentheses and superfluous PHPDoc, nothing semantic. `vendor/bin/pint --test` is a blocking CI job.
- `phpstan.neon`, pinned at level 5 with no baseline. Without a config file PHPStan silently fell back to level 0, so `composer analyse` ran green while 84 real errors sat at level 2 and 81 more at level 3. Analysis needs `--memory-limit=1G`; the `analyse` script now passes it.
- GitHub Actions CI: `composer validate --strict`, a PHP 8.1–8.4 × Laravel 10–12 test matrix including a `prefer-lowest` leg, PHPStan, Pint and `composer audit`. Every matrix leg was resolved with `composer update --dry-run` before being committed.
- `symfony/var-dumper` removed from `require-dev`: imported nowhere, and its `^6.2` constraint made Laravel 12 unresolvable.
- `phpstan/phpstan-phpunit` and `phpstan/phpstan-deprecation-rules` widened to span both majors, so they no longer cap PHPStan at `^1` and block larastan `^3`.
- `psr/http-message` declared. It was imported for `ResponseInterface` without ever being required.
- `config.platform.php` pinned to `8.1.0` and the lock rebuilt against it. The lock had resolved symfony to v7, which needs PHP 8.2, so `vendor/composer/platform_check.php` demanded 8.2 while `composer.json` advertised `^8.1`. Consumers were never affected — Composer only honours the root package's lock — but a contributor on 8.1 could not `composer install`. Cost: symfony 6.4, pint 1.20, phpunit 10.5.36.
- `config.audit.ignore` lists six advisories by id, each with the reason. Laravel 10 is EOL and every 10.x release carries unpatched advisories, so Composer refused to resolve the 8.1 graph at all. Ignoring those specific ids keeps blocking switched on for anything new, unlike turning `audit.block-insecure` off.
- `composer lint` runs `pint --test` rather than rewriting files, and passes `--memory-limit=1G` to PHPStan, which otherwise crashes its parallel worker at the default 128M.

Changed
- `BitfinexResponse::__construct()` accepts `Psr\Http\Message\ResponseInterface` instead of Guzzle's concrete `Response`. This only widens what callers may pass, and matches what a PSR-18 client is declared to return.
- `BitfinexRequest::execute()` and `BitfinexResponse::transformContent()` declare the concrete return type they always produced.
- Four `match` expressions over `BitfinexType` gained a `default` arm. An unexpected type threw `UnhandledMatchError`, a PHP engine error rather than a `BitfinexException`.

Docs
- Document the notification envelope, the two new exceptions and the reconciliation of `cancelMultiple()` in `docs/USAGE.md`.

---

## [v0.2.7] - 2025-09-08

Added
- `TransformerFactory` resolving the public response transformers, with unit tests.
- Additional public transformers and the merchant (Bitfinex Pay) services.

## [v0.2.6] - 2025-09-06

Added
- Public endpoints: candles, configs, derivatives status and its history, liquidations, leaderboards, funding statistics and market average price, each with an entity and a response mapping.
- Authenticated orders completed: update, cancel, multi, cancel-multi, orders history, order trades, trades history and ledgers, plus the `LedgerEntry` entity.
- Authenticated positions, with the `Position` entity.
- Authenticated funding endpoints, with their entities and response mappings.
- Deposit/withdrawal history helpers under `accountAction()`; `movements()` accepts `start`, `end`, `limit` filters.
- Account Actions: transfer between wallets, generate invoice, withdrawal, user settings write/read/delete.
- Merchant (authenticated) support and its service accessor.

Changed
- `RequestBuilder` handles query parameters; the public services were refactored onto it.
- Configuration mappings cover the structured `map`, `list` and `info` modes.
- `Liquidation` carries the full set of API fields.
- Composer scripts: add `clear`, `test:feature`, `test:unit` for Laravel package workflows.
- Tests: use `Tests\TestCase` for the Feature suite.

Docs
- README revamped: installation, quick start, contribution and support.
- `docs/USAGE.md` extended with authenticated usage (deposit addresses, movements, histories, movement details) and positions examples.
- `CHANGELOG.md` replaces the former `changes/changes.txt`.

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
