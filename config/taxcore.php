<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PFX Certificate
    |--------------------------------------------------------------------------
    | Absolute path to the .pfx file and its password.
    | The PEM files extracted at runtime are written to storage/app/taxcore/
    | and are covered by the existing storage/app/.gitignore wildcard.
    */
    'pfx_path'     => env('TAXCORE_PFX_PATH'),
    'pfx_password' => env('TAXCORE_PFX_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Endpoint
    |--------------------------------------------------------------------------
    | When set, this value is used directly instead of reading the URL from
    | the certificate Subject Alternative Name extension (useful for tests).
    */
    'endpoint_override' => env('TAXCORE_ENDPOINT_OVERRIDE'),

    /*
    |--------------------------------------------------------------------------
    | API Paths (relative to the base endpoint)
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | ESDC PIN
    |--------------------------------------------------------------------------
    | The 4-digit PIN assigned to your ESDC instance in the TAP Developer Portal.
    | Sent in the body of every authentication request.
    */
    'pin' => env('TAXCORE_PIN'),

    /*
    |--------------------------------------------------------------------------
    | API Paths (relative to the base endpoint)
    |--------------------------------------------------------------------------
    | The ESDC instance URL already contains /api, so paths start from there.
    | e.g. base = http://devesdc.sandbox.taxcore.online:8888/{token}/api
    |      auth = .../vsdcconfig
    */
    'paths' => [
        'auth'       => env('TAXCORE_PATH_AUTH',       '/v3/pin'),
        'env_params' => env('TAXCORE_PATH_ENV_PARAMS', '/v3/environment-parameters'),
        'invoice'    => env('TAXCORE_PATH_INVOICE',    '/v3/invoices'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Cache TTL (seconds)
    |--------------------------------------------------------------------------
    */
    'token_ttl' => (int) env('TAXCORE_TOKEN_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | External OpenSSL Binary
    |--------------------------------------------------------------------------
    | Path to an openssl.exe/openssl binary used as a fallback when PHP's
    | bundled OpenSSL 3.x cannot read legacy-encrypted PFX files (RC2/3DES).
    | Leave empty to let the service auto-detect Git for Windows' openssl.
    */
    'openssl_bin' => env('TAXCORE_OPENSSL_BIN'),

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    | Set to false only in local/dev environments. Never disable in production.
    */
    'verify_ssl' => env('TAXCORE_VERIFY_SSL', true),
];
