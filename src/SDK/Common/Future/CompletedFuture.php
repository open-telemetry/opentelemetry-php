<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Future;

use Closure;
use Throwable;

/**
 * @template T
 * @template-implements FutureInterface<T>
 */
final class CompletedFuture implements FutureInterface
{
    /**
     * @param T $value
     */
    public function __construct(private $value)
    {
    }

    #[\Override]
    public function await()
    {
        return $this->value;
    }

    #[\Override]
    public function map(Closure $closure): FutureInterface
    {
        $c = $closure;
        unset($closure);

        try {
            return new CompletedFuture($c($this->value));
        } catch (Throwable $e) {
            return new ErrorFuture($e);
        }
    }

    #[\Override]
    public function catch(Closure $closure): FutureInterface
    {
        return $this;
    }
}
