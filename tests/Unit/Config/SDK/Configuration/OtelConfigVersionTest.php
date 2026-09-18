<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration;

use OpenTelemetry\Config\SDK\Configuration\OtelConfigVersion;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OtelConfigVersion::class)]
final class OtelConfigVersionTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Stable versions — must resolve without a deprecation notice
    // -------------------------------------------------------------------------

    #[DataProvider('stableVersionProvider')]
    public function test_stable_version_resolves(string $input, OtelConfigVersion $expected): void
    {
        $deprecations = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$deprecations): bool {
            if ($errno === E_USER_DEPRECATED) {
                $deprecations[] = $errstr;
            }

            return true;
        }, E_USER_DEPRECATED);

        try {
            $result = OtelConfigVersion::fromVersion($input);
        } finally {
            restore_error_handler();
        }

        $this->assertSame($expected, $result);
        $this->assertEmpty($deprecations, 'No deprecation notice must be emitted for stable versions');
    }

    public static function stableVersionProvider(): iterable
    {
        yield '"1.0"'   => ['1.0',   OtelConfigVersion::V1_0];
        yield '"1.0.0"' => ['1.0.0', OtelConfigVersion::V1_0];
        yield '"1.0.5"' => ['1.0.5', OtelConfigVersion::V1_0];
        yield '"1.1"'   => ['1.1',   OtelConfigVersion::V1_1];
        yield '"1.1.0"' => ['1.1.0', OtelConfigVersion::V1_1];
        yield '"1.1.3"' => ['1.1.3', OtelConfigVersion::V1_1];
    }

    // -------------------------------------------------------------------------
    // Pre-release versions — must resolve AND emit E_USER_DEPRECATED
    // -------------------------------------------------------------------------

    #[DataProvider('preReleaseVersionProvider')]
    public function test_pre_release_version_resolves_with_deprecation(string $input, OtelConfigVersion $expected): void
    {
        $deprecations = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$deprecations): bool {
            if ($errno === E_USER_DEPRECATED) {
                $deprecations[] = $errstr;
            }

            return true;
        }, E_USER_DEPRECATED);

        try {
            $result = OtelConfigVersion::fromVersion($input);
        } finally {
            restore_error_handler();
        }

        $this->assertSame($expected, $result);
        $this->assertNotEmpty($deprecations, 'A deprecation notice must be emitted for pre-release versions');
        $this->assertStringContainsString($input, $deprecations[0]);
        $this->assertStringContainsString('deprecated', $deprecations[0]);
    }

    public static function preReleaseVersionProvider(): iterable
    {
        yield '"1.0-rc.1"' => ['1.0-rc.1', OtelConfigVersion::V1_0];
        yield '"1.0-rc.2"' => ['1.0-rc.2', OtelConfigVersion::V1_0];
        yield '"1.1-rc.1"' => ['1.1-rc.1', OtelConfigVersion::V1_1];
    }

    // -------------------------------------------------------------------------
    // Unsupported versions — must throw InvalidArgumentException
    // -------------------------------------------------------------------------

    #[DataProvider('unsupportedVersionProvider')]
    public function test_unsupported_version_throws(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/"' . preg_quote($input, '/') . '"/');

        OtelConfigVersion::fromVersion($input);
    }

    public static function unsupportedVersionProvider(): iterable
    {
        yield '"0.9"' => ['0.9'];
        yield '"1.2"' => ['1.2'];
        yield '"2.0"' => ['2.0'];
    }
}
