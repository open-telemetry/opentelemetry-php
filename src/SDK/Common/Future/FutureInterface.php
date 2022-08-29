<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Future;

use Closure;

/**
 * @template-covariant T
 */
interface FutureInterface
{
    /**
     * @psalm-return T
     */
    public function await();

    /**
     * @psalm-template U
     * @psalm-param Closure(T): U $closure
     * @psalm-return FutureInterface<U>
     *
     * @psalm-suppress InvalidTemplateParam
     */
    public function map(Closure $closure): FutureInterface;

    /**
     * @psalm-template U
     * @psalm-param Closure(\Throwable): U $closure
     * @psalm-return FutureInterface<T|U>
     */
    public function catch(Closure $closure): FutureInterface;
}
