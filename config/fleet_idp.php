<?php

/**
 * Fleet Console defaults for shaferllc/fleet-idp-client.
 *
 * Keep in sync with vendor/shaferllc/fleet-idp-client/config/fleet_idp.php on package upgrades.
 */

return [

    'url' => rtrim((string) env('FLEET_IDP_URL', ''), '/'),

    'client_id' => env('FLEET_IDP_CLIENT_ID', ''),

    'client_secret' => env('FLEET_IDP_CLIENT_SECRET', ''),

    'redirect_uri' => env('FLEET_IDP_REDIRECT_URI'),

    /*
     * Fleet Auth Passport clients for this app typically use /auth/callback (see README).
     */
    'redirect_path' => env('FLEET_IDP_REDIRECT_PATH', '/auth/callback'),

    'password_client_id' => env('FLEET_IDP_PASSWORD_CLIENT_ID', ''),

    'password_client_secret' => env('FLEET_IDP_PASSWORD_CLIENT_SECRET', ''),

    'session_oauth_state_key' => env('FLEET_IDP_SESSION_STATE_KEY', 'fleet_idp_oauth_state'),

    'user_model' => env('FLEET_IDP_USER_MODEL', 'App\\Models\\User'),

    'provider_name' => env('FLEET_IDP_PROVIDER_NAME', 'fleet_auth'),

    'provisioning' => [
        'token' => env('FLEET_AUTH_PROVISIONING_TOKEN', ''),
        'url' => env('FLEET_AUTH_PROVISIONING_URL', ''),
        'merge_request_key' => env('FLEET_IDP_PROVISIONING_REQUEST_KEY', '_fleet_idp_provisioning_password'),
        'password_request_keys' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'FLEET_IDP_PROVISIONING_PASSWORD_KEYS',
            '_fleet_idp_provisioning_password,password,form.password'
        ))))),
    ],

    'web' => [
        'enabled' => env('FLEET_IDP_WEB_ENABLED', true),

        'middleware' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'FLEET_IDP_WEB_MIDDLEWARE',
            'web,fleet.trusted_ip'
        ))))),

        'mode' => env('FLEET_IDP_WEB_MODE', 'session'),

        'start_path' => env('FLEET_IDP_OAUTH_START_PATH', '/oauth/fleet-auth'),

        'failure_path' => env('FLEET_IDP_OAUTH_FAILURE_PATH', '/oauth/fleet-auth/failure'),

        'route_names' => [
            'redirect' => env('FLEET_IDP_ROUTE_OAUTH_REDIRECT', 'fleet-idp.oauth.redirect'),
            'callback' => env('FLEET_IDP_ROUTE_OAUTH_CALLBACK', 'fleet-idp.oauth.callback'),
            'failure' => env('FLEET_IDP_ROUTE_OAUTH_FAILURE', 'fleet-idp.oauth.failure'),
        ],

        'eloquent' => [
            'oauth_error_route' => env('FLEET_IDP_OAUTH_ERROR_ROUTE', 'fleet-idp.oauth.failure'),
            'try_again_route' => env('FLEET_IDP_TRY_AGAIN_ROUTE', 'console.login'),
            'oauth_error_session_key' => env('FLEET_IDP_OAUTH_ERROR_SESSION_KEY', 'oauth_error'),
            'post_login_route' => env('FLEET_IDP_POST_LOGIN_ROUTE', 'console.dashboard'),
            'two_factor_route' => env('FLEET_IDP_TWO_FACTOR_ROUTE', 'two-factor.challenge'),
        ],

        'session' => [
            'error_route' => env('FLEET_IDP_SESSION_OAUTH_ERROR_ROUTE', 'console.login'),
            'error_validation_key' => env('FLEET_IDP_SESSION_ERROR_KEY', 'password'),
            'auth_session_key' => env('FLEET_IDP_SESSION_AUTH_KEY', 'fleet_console_ok'),
            'user_session_key' => env('FLEET_IDP_SESSION_USER_KEY', 'fleet_idp_user'),
            'post_login_route' => env('FLEET_IDP_SESSION_POST_LOGIN_ROUTE', 'console.dashboard'),
        ],
    ],

    'socialite' => [
        'enabled' => filter_var(env('FLEET_IDP_SOCIALITE_ENABLED', true), FILTER_VALIDATE_BOOL),

        'route_prefix' => env('FLEET_IDP_SOCIALITE_ROUTE_PREFIX', 'oauth'),

        'middleware' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'FLEET_IDP_SOCIALITE_MIDDLEWARE',
            'web'
        ))))),

        'providers_url' => env('FLEET_IDP_SOCIALITE_PROVIDERS_URL'),

        'policy_cache_seconds' => max(0, (int) env('FLEET_IDP_SOCIALITE_POLICY_CACHE', 60)),

        'policy_timeout_seconds' => max(1, (int) env('FLEET_IDP_SOCIALITE_POLICY_TIMEOUT', 3)),

        'policy_fail_open' => filter_var(env('FLEET_IDP_SOCIALITE_POLICY_FAIL_OPEN', true), FILTER_VALIDATE_BOOL),

        'null_password_for_social' => filter_var(env('FLEET_IDP_SOCIALITE_NULL_PASSWORD', true), FILTER_VALIDATE_BOOL),

        'user_model' => env('FLEET_IDP_SOCIALITE_USER_MODEL'),

        'error_route' => env('FLEET_IDP_SOCIALITE_ERROR_ROUTE', 'console.login'),

        'oauth_error_session_key' => env('FLEET_IDP_SOCIALITE_ERROR_KEY', 'oauth_error'),

        'post_login_route' => env('FLEET_IDP_SOCIALITE_POST_LOGIN_ROUTE', 'console.dashboard'),

        'two_factor_route' => env('FLEET_IDP_SOCIALITE_TWO_FACTOR_ROUTE', 'two-factor.challenge'),

        'two_factor_session_user_id_key' => env('FLEET_IDP_SOCIALITE_TWO_FACTOR_USER_KEY', 'two_factor.id'),

        'two_factor_session_remember_key' => env('FLEET_IDP_SOCIALITE_TWO_FACTOR_REMEMBER_KEY', 'two_factor.remember'),
    ],

];
