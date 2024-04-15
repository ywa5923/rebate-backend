<?php

namespace Modules\Auth\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Facades\Hash;

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

      //To DO : choose hashing algorithm based on user model settings ($this->model)

    //   return (
    //     $this->hasher->check($plain, $user->getAuthPassword(), ['salt' => $user->salt]) ||
    //     Hash::driver('bcrypt')->check($plain, $user->getAuthPassword(), ['salt' => $user->salt]))?true:false;
     

        return $this->hasher->check($plain, $user->getAuthPassword(), ['salt' => $user->salt]);
    }
}
