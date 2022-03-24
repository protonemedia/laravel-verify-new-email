# Changelog

All notable changes to `laravel-verify-new-email` will be documented in this file

## 1.6.0 - 2022-02-24

- Interact with the Mailable before sending

## 1.5.0 - 2022-02-04

- Support for Laravel 9

## 1.4.0 - 2021-12-19

- Support for PHP 8.1
- Dropped support for PHP 7.3
- Dropped support for Laravel 6 and 7

## 1.3.1 - 2021-12-16

- Redirect bugfix

## 1.3.0 - 2020-10-31

- Support for PHP 8.0
- Dropped support for PHP 7.2

## 1.2.1 - 2020-09-07

- Added `InvalidEmailVerificationModelException`

## 1.2.0 - 2020-09-04

- support for Laravel 8.0

## 1.1.0 - 2020-03-03

- support for Laravel 7.0

## 1.0.7 - 2020-01-08

- support for custom model
- setting to remember login

## 1.0.6 - 2020-01-06

- added `web` middleware to verification route

## 1.0.5 - 2020-01-05

- don't fire event if nothing has changed
- refactoring

## 1.0.4 - 2020-01-05

- publishes the migration only once
- refactoring

## 1.0.3 - 2020-01-01

- added a subject to Mailables

## 1.0.2 - 2019-12-31

- unguarded the PendingUserEmail model

## 1.0.1 - 2019-12-30

- bugfix for sending the wrong Mailable

## 1.0.0 - 2019-12-30

- initial release
