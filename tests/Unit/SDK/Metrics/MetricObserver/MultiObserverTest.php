<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricObserver;

use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Metrics\MetricObserver\MultiObserver;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\MetricObserver\MultiObserver
 */
final class MultiObserverTest extends TestCase
{
    public function test_registered_callbacks_are_invoked(): void
    {
        $multiObserver = new MultiObserver();

        $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(1));
        $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(2));
        $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(3));

        $observer = new ValueCollectingObserver();
        $multiObserver($observer);
        $this->assertSame([1, 2, 3], $observer->values);
    }

    public function test_register_duplicate_callback_is_added_only_once(): void
    {
        $multiObserver = new MultiObserver();

        $callback = static fn (ObserverInterface $observer) => $observer->observe(1);
        $multiObserver->observe($callback);
        $multiObserver->observe($callback);

        $observer = new ValueCollectingObserver();
        $multiObserver($observer);
        $this->assertSame([1], $observer->values);
    }

    public function test_unregistered_callbacks_are_not_invoked(): void
    {
        $multiObserver = new MultiObserver();

        $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(1));
        $token = $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(2));
        $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(3));

        $multiObserver->cancel($token);

        $observer = new ValueCollectingObserver();
        $multiObserver($observer);
        $this->assertSame([1, 3], $observer->values);
    }

    public function test_unregister_with_invalid_token_is_noop(): void
    {
        $multiObserver = new MultiObserver();

        $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(1));
        $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(2));
        $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(3));

        $multiObserver->cancel(-1);

        $observer = new ValueCollectingObserver();
        $multiObserver($observer);
        $this->assertSame([1, 2, 3], $observer->values);
    }

    public function test_unregister_during_collection_is_allowed(): void
    {
        // E.g. when using weak closure and closure $this is garbage collected during metric collection
        $multiObserver = new MultiObserver();

        $multiObserver->observe(static function (ObserverInterface $observer) use ($multiObserver, &$token): void {
            $observer->observe(1);
            /** @phpstan-ignore-next-line */
            $multiObserver->cancel($token);
        });
        $token = $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(2));
        $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(3));

        $observer = new ValueCollectingObserver();
        $multiObserver($observer);
        $this->assertSame([1, 3], $observer->values);
    }
}

final class ValueCollectingObserver implements ObserverInterface
{

    /**
     * @var array<float|int>
     */
    public array $values = [];

    public function observe($amount, iterable $attributes = []): void
    {
        $this->values[] = $amount;
    }
}
