<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use function OpenTelemetry\SDK\Common\Util\isEmpty;
use PHPUnit\Framework\TestCase;

function gen(): iterable
{
    yield "The generator's only element \n";
}

/**
 * @covers \OpenTelemetry\SDK\Common\Util\isEmpty
 */
final class FunctionsTest extends TestCase
{
    public function test_for_empty_iterable(): void
    {
        $this->assertTrue(isEmpty([]));
    }

    public function test_for_exhausted_generator(): void
    {
        $generator = gen();
        foreach ($generator as $value) {
        }
        $value=isEmpty($generator) ? true : false;
        $this->assertTrue($value);
    }

    public function test_for_non_empty():void
    {
        $this->assertFalse(isEmpty([1,2]));
    }
}
