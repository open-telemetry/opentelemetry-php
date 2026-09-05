<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration;

use OpenTelemetry\Config\SDK\Configuration\SemVer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SemVer::class)]
final class SemVerTest extends TestCase
{
    // -------------------------------------------------------------------------
    // gte
    // -------------------------------------------------------------------------

    #[DataProvider('gteProvider')]
    public function test_gte(string $version, string $constraint, bool $expected): void
    {
        $this->assertSame($expected, SemVer::gte($version, $constraint));
    }

    public static function gteProvider(): iterable
    {
        // exact match is always >=
        yield 'equal: 1.1 >= 1.1'               => ['1.1', '1.1', true];
        yield 'equal: 1.0-rc.2 >= 1.0-rc.2'     => ['1.0-rc.2', '1.0-rc.2', true];

        // higher version satisfies >=
        yield 'higher: 1.1 >= 1.0-rc.2'         => ['1.1', '1.0-rc.2', true];
        yield 'higher: 2.0 >= 1.1'              => ['2.0', '1.1', true];

        // pre-release is less than its release — 1.0-rc.2 < 1.1
        yield 'pre-release: NOT 1.0-rc.2 >= 1.1' => ['1.0-rc.2', '1.1', false];

        // lower release does not satisfy >=
        yield 'lower: NOT 1.0 >= 1.1'           => ['1.0', '1.1', false];
    }

    // -------------------------------------------------------------------------
    // lt
    // -------------------------------------------------------------------------

    #[DataProvider('ltProvider')]
    public function test_lt(string $version, string $constraint, bool $expected): void
    {
        $this->assertSame($expected, SemVer::lt($version, $constraint));
    }

    public static function ltProvider(): iterable
    {
        // equal is not <
        yield 'equal: NOT 1.1 < 1.1'            => ['1.1', '1.1', false];

        // pre-release is less than the next release — the critical path for enabled/disabled normalization
        yield 'pre-release: 1.0-rc.2 < 1.1'     => ['1.0-rc.2', '1.1', true];

        // lower release is <
        yield 'lower: 1.0 < 1.1'               => ['1.0', '1.1', true];

        // higher version is not <
        yield 'higher: NOT 2.0 < 1.1'          => ['2.0', '1.1', false];
    }

    // -------------------------------------------------------------------------
    // eq
    // -------------------------------------------------------------------------

    #[DataProvider('eqProvider')]
    public function test_eq(string $version, string $constraint, bool $expected): void
    {
        $this->assertSame($expected, SemVer::eq($version, $constraint));
    }

    public static function eqProvider(): iterable
    {
        yield 'equal: 1.1 == 1.1'               => ['1.1', '1.1', true];
        yield 'equal: 1.0-rc.2 == 1.0-rc.2'     => ['1.0-rc.2', '1.0-rc.2', true];

        // different versions are not equal
        yield 'NOT equal: 1.0-rc.2 == 1.1'      => ['1.0-rc.2', '1.1', false];
        yield 'NOT equal: 1.1 == 1.0-rc.2'      => ['1.1', '1.0-rc.2', false];
        yield 'NOT equal: 1.0 == 1.1'           => ['1.0', '1.1', false];
    }

    // -------------------------------------------------------------------------
    // Critical invariant: file_format "1.0-rc.2" must sort before "1.1"
    // -------------------------------------------------------------------------

    public function test_v1_0_rc2_is_less_than_v1_1(): void
    {
        $this->assertTrue(SemVer::lt('1.0-rc.2', '1.1'), '"1.0-rc.2" must be < "1.1"');
        $this->assertFalse(SemVer::gte('1.0-rc.2', '1.1'), '"1.0-rc.2" must not be >= "1.1"');
    }

    public function test_v1_1_satisfies_v1_1_constraint(): void
    {
        $this->assertTrue(SemVer::gte('1.1', '1.1'), '"1.1" must satisfy >= "1.1"');
        $this->assertFalse(SemVer::lt('1.1', '1.1'), '"1.1" must not be < "1.1"');
    }
}
