<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Database\Eloquent\Model;

class PendingEmailAddress extends Model
{
    const UPDATED_AT = null;

    public function scopeForUser($query, Model $user)
    {
        $query->where([
            $this->qualifyColumn('user_type') => get_class($user),
            $this->qualifyColumn('user_id')   => $user->getKey(),
        ]);
    }

    public function activate()
    {
        $this->user->email = $this->email;

        static::forUser($this->user)->get()->each->delete();
        static::whereEmail($this->email)->get()->each->delete();

        $this->user->email_confirmed_at = now();
        $this->user->save();
    }
}
