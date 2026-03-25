<?php

namespace Tests\Unit;

use App\Support\FleetOperatorSummaryDisplay;
use PHPUnit\Framework\TestCase;

class FleetOperatorSummaryDisplayTest extends TestCase
{
    public function test_uptime_label_formats_seconds(): void
    {
        $this->assertSame('0m', FleetOperatorSummaryDisplay::uptimeLabel(0));
        $this->assertSame('1d 2h 3m', FleetOperatorSummaryDisplay::uptimeLabel(86400 + 7200 + 180));
        $this->assertNull(FleetOperatorSummaryDisplay::uptimeLabel('x'));
    }

    public function test_short_commit_truncates_long_sha(): void
    {
        $this->assertSame('abcdef0', FleetOperatorSummaryDisplay::shortCommit('abcdef0123456789'));
        $this->assertSame('abc', FleetOperatorSummaryDisplay::shortCommit('abc'));
        $this->assertNull(FleetOperatorSummaryDisplay::shortCommit('  '));
    }

    public function test_normalized_dependencies(): void
    {
        $deps = FleetOperatorSummaryDisplay::normalizedDependencies([
            'dependencies' => [
                ['name' => 'db', 'ok' => true],
                ['name' => 'cache', 'ok' => false, 'detail' => 'timeout'],
                ['id' => 'queue', 'healthy' => 0],
            ],
        ]);
        $this->assertCount(3, $deps);
        $this->assertTrue($deps[0]['ok']);
        $this->assertFalse($deps[1]['ok']);
        $this->assertSame('timeout', $deps[1]['detail']);
        $this->assertFalse($deps[2]['ok']);
    }

    public function test_normalized_links_filters_invalid_urls(): void
    {
        $links = FleetOperatorSummaryDisplay::normalizedLinks([
            'links' => [
                'Docs' => 'https://example.com/docs',
                'Bad' => 'not-a-url',
            ],
        ]);
        $this->assertSame(['Docs' => 'https://example.com/docs'], $links);
    }
}
