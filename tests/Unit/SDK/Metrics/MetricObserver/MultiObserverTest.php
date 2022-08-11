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

    public function test_unregister_register_during_collection_does_not_trigger_old_callback(): void
    {
        $multiObserver = new MultiObserver();

        $multiObserver->observe(static function (ObserverInterface $observer) use ($multiObserver, &$token): void {
            $observer->observe(1);
            $multiObserver->cancel($token);
            $token = $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(2));
        });
        $token = $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(2));
        $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(3));

        $observer = new ValueCollectingObserver();
        $multiObserver($observer);
        $this->assertSame([1, 3], $observer->values);
    }

    public function test_has_returns_true_for_registered_callback(): void
    {
        $multiObserver = new MultiObserver();

        $token = $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(1));
        $this->assertTrue($multiObserver->has($token));
    }

    public function test_has_returns_false_for_canceled_callback(): void
    {
        $multiObserver = new MultiObserver();

        $token = $multiObserver->observe(static fn (ObserverInterface $observer) => $observer->observe(1));
        $multiObserver->cancel($token);
        $this->assertFalse($multiObserver->has($token));
    }

    public function test_multiple_calls_to_destructors_return_same_instance(): void
    {
        $multiObserver = new MultiObserver();

        $a = $multiObserver->destructors();
        $b = $multiObserver->destructors();
        $this->assertSame($a, $b);
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
