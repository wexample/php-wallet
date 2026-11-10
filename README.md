# php-wallet

Version: 2.0.0

The credentials an application holds — named, typed, checked against their type —, the twin of wexample-wallet

## Table of Contents

- [Introduction](#introduction)

`php-wallet` holds the credentials a PHP application hands to what it runs: named credentials, each of a type saying which parameters it carries — an `open_ai` one with an `api_key`, a `postgres` one with a host, a port, a database, a user and a password. It is the twin of the Python `wexample-wallet`: the same credentials, the same types, read the same way on both sides.

The application builds the wallet at startup — from a file, a secret manager, a database — and passes it on; nothing is read from the environment behind its back. Credential types, when given, check that each credential carries its required parameters, so a wallet fails when built rather than on first use. A type also says which parameters are secrets, which have a default and which take a closed set of values: what a screen editing credentials needs to draw its fields.

The framework integration lives elsewhere: `symfony-wallet` keeps the credentials as Doctrine entities and builds the wallet from them.
