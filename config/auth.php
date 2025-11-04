<?php



return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            //'driver' => 'eloquent',
            'driver'=>'fx_user_provider',
            'model' => env('AUTH_MODEL', Modules\Auth\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    /*
    |--------------------------------------------------------------------------
    | JWT Token Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for JWT tokens generated after magic link verification
    |
    */

    'jwt_expiration_days' => (int) env('JWT_EXPIRATION_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Magic Link Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Set to false to prevent magic links from being marked as used after verification.
    | This allows the same magic link to be used multiple times for testing.
    | WARNING: Only use this in development/testing environments!
    |
    */

    'mark_magic_link_as_used' => env('MARK_MAGIC_LINK_AS_USED', true),

    /*
    |--------------------------------------------------------------------------
    | Magic Link Expiration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for magic link expiration hours for different user types.
    | These values determine how long magic links remain valid before expiring.
    |
    */

    'magic_link_expiration_hours_for_broker_team_user' => (int) env('MAGIC_LINK_EXPIRATION_HOURS_FOR_BROKER_TEAM_USER', 24),
    'magic_link_expiration_hours_for_platform_user' => (int) env('MAGIC_LINK_EXPIRATION_HOURS_FOR_PLATFORM_USER', 24),

];
