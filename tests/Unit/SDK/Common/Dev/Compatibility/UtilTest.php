<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Dev\Compatibility;

use OpenTelemetry\SDK\Common\Dev\Compatibility\Util;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Dev\Compatibility\Util
 */
class UtilTest extends TestCase
{
    public function test_trigger_class_deprecation_notice(): void
    {
        $this->expectNotice();

        Util::triggerClassDeprecationNotice(Util::class);
    }
}
