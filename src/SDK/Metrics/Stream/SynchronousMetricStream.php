<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use function assert;
use function extension_loaded;
use GMP;
use function gmp_init;
use function is_int;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use const PHP_INT_SIZE;
use function sprintf;

/**
 * @internal
 * @phan-file-suppress PhanUndeclaredTypeParameter, PhanUndeclaredTypeProperty
 */
final class SynchronousMetricStream implements MetricStreamInterface
{
    use LogsMessagesTrait;

    private readonly DeltaStorage $delta;
    private int|GMP $readers = 0;
    private int|GMP $cumulative = 0;

    /**
     * @todo rector mistakenly makes $timestamp readonly, which conflicts with `self::push`. disabled in rector.php
     */
    public function __construct(
        private readonly AggregationInterface $aggregation,
        private int $timestamp,
    ) {
        $this->delta = new DeltaStorage($this->aggregation);
    }

    #[\Override]
    public function temporality(): Temporality|string
    {
        return Temporality::DELTA;
    }

    #[\Override]
    public function timestamp(): int
    {
        return $this->timestamp;
    }

    #[\Override]
    public function push(Metric $metric): void
    {
        [$this->timestamp, $metric->timestamp] = [$metric->timestamp, $this->timestamp];
        $this->delta->add($metric, $this->readers);
    }

    #[\Override]
    public function register($temporality): int
    {
        $reader = 0;
        for ($r = $this->readers; ($r & 1) != 0; $r >>= 1, $reader++) {
        }

        if ($reader === (PHP_INT_SIZE << 3) - 1 && is_int($this->readers)) {
            if (!extension_loaded('gmp')) {
                self::logWarning(sprintf('GMP extension required to register over %d readers', (PHP_INT_SIZE << 3) - 1));
                $reader = PHP_INT_SIZE << 3;
            } else {
                assert(is_int($this->cumulative));
                $this->readers = gmp_init($this->readers);
                $this->cumulative = gmp_init($this->cumulative);
            }
        }

        $readerMask = ($this->readers & 1 | 1) << $reader;
        $this->readers ^= $readerMask;
        if ($temporality === Temporality::CUMULATIVE) {
            $this->cumulative ^= $readerMask;
        }

        return $reader;
    }

    #[\Override]
    public function unregister(int $reader): void
    {
        $readerMask = ($this->readers & 1 | 1) << $reader;
        if (($this->readers & $readerMask) == 0) {
            return;
        }

        $this->delta->collect($reader);

        $this->readers ^= $readerMask;
        if (($this->cumulative & $readerMask) != 0) {
            $this->cumulative ^= $readerMask;
        }
    }

    #[\Override]
    public function collect(int $reader): DataInterface
    {
        $cumulative = ($this->cumulative >> $reader & 1) != 0;
        $metric = $this->delta->collect($reader, $cumulative) ?? new Metric([], [], $this->timestamp);

        $temporality = $cumulative
            ? Temporality::CUMULATIVE
            : Temporality::DELTA;

        return $this->aggregation->toData(
            $metric->attributes,
            $metric->summaries,
            Exemplar::groupByIndex($metric->exemplars),
            $metric->timestamp,
            $this->timestamp,
            $temporality,
        );
    }
}
