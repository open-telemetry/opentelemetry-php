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
        $this->expectDeprecation();

        Util::triggerClassDeprecationNotice(Util::class, self::class);
    }

    public function test_trigger_method_deprecation_notice_without_class(): void
    {
        $this->expectDeprecation();

        Util::triggerMethodDeprecationNotice(Util::class, __METHOD__);
    }

    public function test_trigger_method_deprecation_notice_with_class(): void
    {
        $this->expectDeprecation();

        Util::triggerMethodDeprecationNotice(Util::class, 'foo', self::class);
    }
}
