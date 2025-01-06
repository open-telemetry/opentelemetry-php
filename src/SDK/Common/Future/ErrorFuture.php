<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Future;

use Closure;
use Throwable;

/**
 * @psalm-suppress MissingTemplateParam
 */
final class ErrorFuture implements FutureInterface
{
    public function __construct(private readonly Throwable $throwable)
    {
    }

    public function await(): never
    {
        throw $this->throwable;
    }

    public function map(Closure $closure): FutureInterface
    {
        return $this;
    }

    public function catch(Closure $closure): FutureInterface
    {
        $c = $closure;
        unset($closure);

        try {
            return new CompletedFuture($c($this->throwable));
        } catch (Throwable $e) {
            return new ErrorFuture($e);
        }
    }
}
