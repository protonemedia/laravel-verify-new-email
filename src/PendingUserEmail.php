<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Database\Eloquent\Model;

class PendingUserEmail extends Model
{
    /**
     * This model won't be updated.
     */
    const UPDATED_AT = null;

    /**
     * User relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user()
    {
        return $this->morphTo('user');
    }

    /**
     * Scope for the user.
     *
     * @param $query
     * @param \Illuminate\Database\Eloquent\Model $user
     * @return void
     */
    public function scopeForUser($query, Model $user)
    {
        $query->where([
            $this->qualifyColumn('user_type') => get_class($user),
            $this->qualifyColumn('user_id')   => $user->getKey(),
        ]);
    }

    /**
     * Updates the associated user and removes all pending models with this email.
     *
     * @return void
     */
    public function activate()
    {
        $user = tap($this->user, function ($user) {
            $user->email = $this->email;
            $user->email_verified_at = now();
            $user->save();
        });

        static::whereEmail($this->email)->get()->each->delete();
    }
}
