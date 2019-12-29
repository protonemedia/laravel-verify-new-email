# [WIP] Laravel Verify New Email

[![Latest Version on Packagist](https://img.shields.io/packagist/v/protonemedia/laravel-verify-new-email.svg?style=flat-square)](https://packagist.org/packages/protonemedia/laravel-verify-new-email)
[![Build Status](https://img.shields.io/travis/pascalbaljetmedia/laravel-verify-new-email/master.svg?style=flat-square)](https://travis-ci.org/pascalbaljetmedia/laravel-verify-new-email)
[![Quality Score](https://img.shields.io/scrutinizer/g/pascalbaljetmedia/laravel-verify-new-email.svg?style=flat-square)](https://scrutinizer-ci.com/g/pascalbaljetmedia/laravel-verify-new-email)
[![Total Downloads](https://img.shields.io/packagist/dt/protonemedia/laravel-verify-new-email.svg?style=flat-square)](https://packagist.org/packages/protonemedia/laravel-verify-new-email)

Laravel supports verifying email addresses out of the box. This package adds support for verifying new email addresses, for example when a user decides to update his email address. Requires Laravel 6.0 and PHP 7.2 or higher.

## Installation

You can install the package via composer:

```bash
composer require protonemedia/laravel-verify-new-email
```

## Configuration

Publish the database migration, config file and email view:

```bash
php artisan vendor:publish --provider="ProtoneMedia\LaravelVerifyNewEmail\ServiceProvider"
```

## Usage

Add the `MustVerifyNewEmail` trait to your `User` model:

``` php
<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use ProtoneMedia\LaravelVerifyNewEmail\MustVerifyNewEmail;

class User extends Authenticatable
{
    use MustVerifyNewEmail, Notifiable;
}
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email pascal@protone.media instead of using the issue tracker.

## Credits

- [Pascal Baljet](https://github.com/protonemedia)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.