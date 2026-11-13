# Changelog

All notable changes to `api-response` will be documented in this file

## Unreleased

- Support Laravel 10, 11, 12 and 13.2+ with bounded dependency constraints.
- Fix message resolution across Response Builder versions and isolate Facade state.
- Unify exception timestamps, preserve HTTP status codes and headers, and return 405 for unsupported methods.
- Register authentication handling, retain validation field errors and translate mapped exception messages.
- Correct configuration merging and add a named configuration publishing tag.
- Preserve the documented HTTP 200 business-error helper behavior.
- Add integration tests, a Laravel/PHP CI matrix and updated installation instructions.

Upgrade note: PHP 8.1+ is now required (Laravel 11/12 require 8.2+, Laravel 13 requires 8.3+).
Validation responses now include all field errors under `data.messages`.
