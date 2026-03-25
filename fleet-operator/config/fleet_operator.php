<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Operator bearer token
    |--------------------------------------------------------------------------
    |
    | Must match the operator token Fleet Console stores for this service
    | (Console → Services → Edit for that app).
    | Leave null to disable the operator routes (middleware will reject).
    |
    | Summary/readme JSON contracts: see `resources/openapi.yaml` in this package.
    |
    */

    'token' => env('FLEET_OPERATOR_TOKEN'),

];
