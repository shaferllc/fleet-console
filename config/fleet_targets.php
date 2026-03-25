<?php

/**
 * Fleet apps that expose operator summary (and readme) JSON (see PRODUCTS.md).
 *
 * Base URLs use FLEET_CONSOLE_TARGET_URL_TEMPLATE ({key} = directory / Valet name).
 * Optional `operator_path_prefix`: default `/api/operator`; Dply uses `/api/v1/operator`.
 * Optional `site_url`: override link target for “Open site” (defaults to `base_url`).
 */
return [
    ['key' => 'beacon', 'name' => 'Beacon', 'description' => 'Status pages and incident communication.'],
    ['key' => 'drift', 'name' => 'Drift', 'description' => 'Preview environments from branches and PRs.'],
    ['key' => 'dply', 'name' => 'Dply', 'description' => 'BYO servers, sites, and SSH deploys.', 'operator_path_prefix' => '/api/v1/operator'],
    ['key' => 'foghorn', 'name' => 'Foghorn', 'description' => 'Alert routing: rules to Slack, webhooks, email, and more.'],
    ['key' => 'harbor', 'name' => 'Harbor', 'description' => 'Git-to-server Laravel/PHP deploys over SSH.'],
    ['key' => 'harbormaster', 'name' => 'Harbormaster', 'description' => 'Webhook bins: capture, relay, replay, and audit.'],
    ['key' => 'jetty', 'name' => 'Jetty', 'description' => 'Secure tunnels to stable public URLs.'],
    ['key' => 'lantern', 'name' => 'Lantern', 'description' => 'Web push and multi-channel team notifications.'],
    ['key' => 'lookout', 'name' => 'Lookout', 'description' => 'Error tracking, grouping, and alerts.'],
    ['key' => 'lookout-monitor', 'name' => 'Lookout Monitor', 'description' => 'Uptime, SSL, heartbeats, and monitoring.'],
    ['key' => 'mooring', 'name' => 'Mooring', 'description' => 'Encrypted secrets and shared config per project.'],
    ['key' => 'pilot', 'name' => 'Pilot', 'description' => 'Feature flags and per-environment rollouts.'],
    ['key' => 'waypost', 'name' => 'Waypost', 'description' => 'Public roadmaps, tasks, and changelogs.'],
];
