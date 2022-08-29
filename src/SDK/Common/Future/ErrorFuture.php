<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Future;

use Closure;
use Throwable;

final class ErrorFuture implements FutureInterface
{
    private Throwable $throwable;

    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    public function await()
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
