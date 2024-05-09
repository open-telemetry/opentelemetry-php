<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Future;

use Exception;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Common\Future\CompletedFuture::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Common\Future\ErrorFuture::class)]
final class FutureTest extends TestCase
{
    public function test_future_await(): void
    {
        $future = new CompletedFuture(2);

        $this->assertSame(2, $future->await());
    }

    public function test_future_await_error(): never
    {
        $future = new ErrorFuture(new Exception());

        $this->expectException(Exception::class);
        $future->await();
    }

    public function test_future_map(): void
    {
        $future = new CompletedFuture(2);
        $future = $future->map(fn ($x) => $x * 3);

        $this->assertSame(6, $future->await());
    }

    public function test_future_map_throw_is_rethrown_on_wait(): void
    {
        $future = new CompletedFuture(2);
        $future = $future->map(function (): never {
            throw new Exception();
        });

        $this->expectException(Exception::class);
        $future->await();
    }

    public function test_future_map_error_is_noop(): void
    {
        $future = new ErrorFuture(new Exception());
        $future = $future->map(function (): never {
            $this->fail();
        });

        $this->expectException(Exception::class);
        $future->await();
    }

    public function test_future_catch(): void
    {
        $exception = new Exception();
        $future = new ErrorFuture($exception);
        $future = $future->catch(fn ($e) => $e);

        $this->assertSame($exception, $future->await());
    }

    public function test_future_catch_throw_is_rethrown_on_wait(): void
    {
        $future = new ErrorFuture(new Exception());
        $future = $future->catch(function (): never {
            throw new Exception('from catch');
        });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('from catch');
        $future->await();
    }

    public function test_future_catch_no_error_is_noop(): void
    {
        $future = new CompletedFuture(2);
        $future = $future->catch(function (): never {
            $this->fail();
        });

        $this->assertSame(2, $future->await());
    }
}
