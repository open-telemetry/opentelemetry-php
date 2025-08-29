<?php

declare(strict_types=1);

namespace Unit\SemConv;

use OpenTelemetry\SemConv\Version;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Version::class)]
class VersionTest extends TestCase
{
    public function test_url(): void
    {
        $url = Version::VERSION_1_25_0->url();
        $this->assertSame('https://opentelemetry.io/schemas/1.25.0', $url);
    }
}
