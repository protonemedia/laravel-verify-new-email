<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use ProtoneMedia\LaravelVerifyNewEmail\MustVerifyNewEmail;

class User extends Authenticatable
{
    use MustVerifyNewEmail, Notifiable;
}
