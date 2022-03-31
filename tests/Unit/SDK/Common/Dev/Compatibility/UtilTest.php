<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Dev\Compatibility;

use Generator;
use OpenTelemetry\SDK\Common\Dev\Compatibility\Util;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Dev\Compatibility\Util
 */
class UtilTest extends TestCase
{
    public function tearDown(): void
    {
        Util::setErrorLevel();
    }

    /**
     * @dataProvider errorLevelProvider
     */
    public function test_set_error_level(int $level): void
    {
        Util::setErrorLevel($level);

        $this->assertSame(
            $level,
            Util::getErrorLevel()
        );
    }

    /**
     * @dataProvider errorLevelProvider
     */
    public function test_trigger_class_deprecation_notice(int $level, string $expectedError): void
    {
        Util::setErrorLevel($level);

        $this->{$expectedError}();

        Util::triggerClassDeprecationNotice(Util::class, self::class);
    }

    /**
     * @dataProvider errorLevelProvider
     */
    public function test_trigger_method_deprecation_notice_without_class(int $level, string $expectedError): void
    {
        Util::setErrorLevel($level);

        $this->{$expectedError}();

        Util::triggerMethodDeprecationNotice(Util::class, __METHOD__);
    }

    /**
     * @dataProvider errorLevelProvider
     */
    public function test_trigger_method_deprecation_notice_with_class(int $level, string $expectedError): void
    {
        Util::setErrorLevel($level);

        $this->{$expectedError}();

        Util::triggerMethodDeprecationNotice(Util::class, 'foo', self::class);
    }


    public function errorLevelProvider(): Generator
    {
        yield [E_USER_DEPRECATED, 'expectDeprecation'];
        yield [E_USER_NOTICE, 'expectNotice'];
        yield [E_USER_WARNING, 'expectWarning'];
        yield [E_USER_ERROR, 'expectError'];
    }

    public function test_turn_errors_off(): void
    {
        $this->expectNotToPerformAssertions();

        Util::setErrorLevel(Util::E_NONE);

        Util::triggerClassDeprecationNotice(Util::class, self::class);
        Util::triggerMethodDeprecationNotice(Util::class, __METHOD__);
        Util::triggerMethodDeprecationNotice(Util::class, 'foo', self::class);
    }
}
