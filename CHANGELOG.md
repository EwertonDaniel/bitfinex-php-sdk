# Changelog

## Unreleased

- Feature: Add filtered deposit/withdrawal history helpers under `accountAction()` and accept `start`, `end`, `limit` on `movements()`.
- Feature: Implement remaining Account Actions endpoints: transfer between wallets, generate invoice, withdrawal, user settings write/read/delete.
- Docs: Extend `docs/USAGE.md` with authenticated usage examples (deposit addresses, movements, deposit/withdrawal history, movement details).
- Tests: Configure Pest to use `Tests\\TestCase` for Feature suite; minor cleanup in datasets.
- Composer: Add scripts `clear`, `test:feature`, and `test:unit` for Laravel package workflows.

Note: Removed temporary `changes/changes.txt` in favor of this `CHANGELOG.md`.
