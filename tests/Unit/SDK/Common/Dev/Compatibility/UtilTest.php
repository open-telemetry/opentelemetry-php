<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Dev\Compatibility;

use Exception;
use Generator;
use OpenTelemetry\SDK\Common\Dev\Compatibility\Util;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Util::class)]
class UtilTest extends TestCase
{
    #[\Override]
    public function setUp(): void
    {
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new Exception($errstr, $errno);
        }, E_USER_WARNING|E_USER_NOTICE|E_USER_ERROR|E_USER_DEPRECATED);
    }

    #[\Override]
    public function tearDown(): void
    {
        Util::setErrorLevel();
        restore_error_handler();
    }

    #[DataProvider('errorLevelProvider')]
    public function test_set_error_level(int $level): void
    {
        Util::setErrorLevel($level);

        $this->assertSame(
            $level,
            Util::getErrorLevel()
        );
    }

    public function test_set_error_level_throws_exception_on_incorrect_level(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Util::setErrorLevel(1);
    }

    #[DataProvider('errorLevelProvider')]
    public function test_trigger_class_deprecation_notice(int $level): void
    {
        Util::setErrorLevel($level);

        $this->expectException(Exception::class);

        Util::triggerClassDeprecationNotice(Util::class, self::class);
    }

    #[DataProvider('errorLevelProvider')]
    public function test_trigger_method_deprecation_notice_without_class(int $level): void
    {
        Util::setErrorLevel($level);

        $this->expectException(Exception::class);

        Util::triggerMethodDeprecationNotice(Util::class, __METHOD__);
    }

    #[DataProvider('errorLevelProvider')]
    public function test_trigger_method_deprecation_notice_with_class(int $level): void
    {
        Util::setErrorLevel($level);

        $this->expectException(Exception::class);

        Util::triggerMethodDeprecationNotice(Util::class, 'foo', self::class);
    }

    public static function errorLevelProvider(): Generator
    {
        yield [E_USER_DEPRECATED];
        yield [E_USER_NOTICE];
        yield [E_USER_WARNING];
        yield [E_USER_ERROR];
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
