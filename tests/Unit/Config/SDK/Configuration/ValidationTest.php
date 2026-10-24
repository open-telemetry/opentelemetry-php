<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\Configuration;

use InvalidArgumentException;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validation::class)]
final class ValidationTest extends TestCase
{
    #[DataProvider('numberProvider')]
    public function test_ensure_number(mixed $value, int|float|null $expected): void
    {
        $this->assertSame($expected, Validation::ensureNumber()($value));
    }

    public static function numberProvider(): iterable
    {
        yield 'null' => [null, null];
        yield 'int' => [1, 1];
        yield 'float' => [1.5, 1.5];
        yield 'numeric int string' => ['8080', 8080];
        yield 'numeric float string' => ['1.5', 1.5];
    }

    public function test_ensure_number_rejects_non_numeric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Validation::ensureNumber()('one');
    }
}
