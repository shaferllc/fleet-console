<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Operator bearer token
    |--------------------------------------------------------------------------
    |
    | Must match FLEET_OPERATOR_TOKEN on the Fleet Console host and the value
    | you configure for this service in the console (or per-target token).
    | Leave null to disable the operator routes (middleware will reject).
    |
    */

    'token' => env('FLEET_OPERATOR_TOKEN'),

];
