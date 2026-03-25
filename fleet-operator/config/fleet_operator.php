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
    */

    'token' => env('FLEET_OPERATOR_TOKEN'),

];
