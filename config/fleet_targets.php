<?php

/**
 * Example services shown before you add rows in the console (or override via FLEET_CONSOLE_TARGETS).
 *
 * Base URLs use FLEET_CONSOLE_TARGET_URL_TEMPLATE ({key} is substituted per row).
 * Optional `operator_path_prefix`: default `/api/operator` (some stacks use e.g. `/api/v1/operator`).
 * Optional `site_url`: override the “Open site” link (defaults to `base_url`).
 */
return [
    [
        'key' => 'example-api',
        'name' => 'Example API',
        'description' => 'Replace this list in config or manage targets under Console → Services.',
    ],
];
