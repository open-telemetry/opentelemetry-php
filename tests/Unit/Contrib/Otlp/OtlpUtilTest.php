<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\OtlpUtil
 */
class OtlpUtilTest extends TestCase
{
    public function test_get_user_agent_header(): void
    {
        $header = OtlpUtil::getUserAgentHeader();
        $this->assertArrayHasKey('User-Agent', $header);
        $this->assertNotNull($header['User-Agent']);
    }
}
