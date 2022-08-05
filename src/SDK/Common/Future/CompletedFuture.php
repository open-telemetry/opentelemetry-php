<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Future;

/**
 * @template T
 * @template-implements FutureInterface<T>
 */
final class CompletedFuture implements FutureInterface
{
    /** @var T */
    private $value;

    /**
     * @param T $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function await(?CancellationInterface $cancellation = null)
    {
        return $this->value;
    }
}
