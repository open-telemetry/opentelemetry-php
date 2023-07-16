<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

/**
 * An observed callback.
 *
 * Callbacks that are bound to an object are automatically detached when the
 * `ObservableCallbackInterface` and the bound object are out of scope.
 * This means that the `ObservableCallbackInterface` can be ignored if the
 * observed callback should be bound to the lifetime of the object.
 * ```php
 * class Example {
 *     function __construct(MeterProviderInterface $meterProvider) {
 *         $meterProvider->getMeter('example')
 *             ->createObservableGauge('random')
 *             ->observe(fn(ObserverInterface $observer)
 *                     => $observer->observe(rand(0, 10)));
 *     }
 * }
 * ```
 * Keeping a reference to the `ObservableCallbackInterface` within the bound
 * object to gain a more fine-grained control over the life-time of the callback
 * does not prevent garbage collection (but might require cycle collection).
 *
 * Unbound (static) callbacks must be detached manually using
 * {@link ObservableCallbackInterface::detach()}.
 * ```php
 * class Example {
 *     private ObservableCallbackInterface $gauge;
 *     function __construct(MeterProviderInterface $meterProvider) {
 *         $this->gauge = $meterProvider->getMeter('example')
 *             ->createObservableGauge('random')
 *             ->observe(static fn(ObserverInterface $observer)
 *                     => $observer->observe(rand(0, 10)));
 *     }
 *     function __destruct() {
 *         $this->gauge->detach();
 *     }
 * }
 * ```
 *
 * @see ObservableCounterInterface::observe()
 * @see ObservableGaugeInterface::observe()
 * @see ObservableUpDownCounterInterface::observe()
 */
interface ObservableCallbackInterface
{

    /**
     * Detaches the associated callback from the instrument.
     */
    public function detach(): void;
}
