<?php

namespace Modules\Auth\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;


class FxUserProvider extends EloquentUserProvider
{
   /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials): bool
    {
        if (is_null($plain = $credentials['password'])) {
            return false;
        } 

       //TO DO: try both hasher algorithms

        return $this->hasher->check($plain, $user->getAuthPassword(), ['salt' => $user->salt]);
    }
}
