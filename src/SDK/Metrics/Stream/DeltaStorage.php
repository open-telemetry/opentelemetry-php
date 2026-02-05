<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use function assert;
use GMP;
use OpenTelemetry\SDK\Metrics\AggregationInterface;

/**
 * @internal
 * @phan-file-suppress PhanUndeclaredTypeParameter, PhanUndeclaredTypeProperty
 */
final class DeltaStorage
{
    private readonly Delta $head;

    public function __construct(private readonly AggregationInterface $aggregation)
    {
        $this->head = new Delta(new Metric([], [], 0), 0);

        /** @phan-suppress-next-line PhanTypeObjectUnsetDeclaredProperty */
        unset($this->head->metric);
    }

    public function add(Metric $metric, int|GMP $readers): void
    {
        /** @phpstan-ignore-next-line */
        if ($readers == 0) {
            return;
        }

        if (($this->head->prev->readers ?? null) != $readers) {
            $this->head->prev = new Delta($metric, $readers, $this->head->prev);
        } else {
            assert($this->head->prev !== null);
            $this->mergeInto($this->head->prev->metric, $metric);
        }
    }

    public function collect(int $reader, bool $retain = false): ?Metric
    {
        $n = null;
        for ($d = $this->head; $d->prev; $d = $d->prev) {
            if (($d->prev->readers >> $reader & 1) != 0) {
                if ($n !== null) {
                    assert($n->prev !== null);
                    $n->prev->readers ^= $d->prev->readers;
                    $this->mergeInto($d->prev->metric, $n->prev->metric);
                    $this->tryUnlink($n);

                    if ($n->prev === $d->prev) {
                        continue;
                    }
                }

                $n = $d;
            }
        }

        $metric = $n->prev->metric ?? null;

        if (!$retain && $n) {
            assert($n->prev !== null);
            $n->prev->readers ^= ($n->prev->readers & 1 | 1) << $reader;
            $this->tryUnlink($n);
        }

        return $metric;
    }

    private function tryUnlink(Delta $n): void
    {
        assert($n->prev !== null);
        /** @phpstan-ignore-next-line */
        if ($n->prev->readers == 0) {
            $n->prev = $n->prev->prev;

            return;
        }

        for ($c = $n->prev->prev;
            $c && ($n->prev->readers & $c->readers) == 0;
            $c = $c->prev) {
        }

        if ($c && $n->prev->readers === $c->readers) {
            $this->mergeInto($c->metric, $n->prev->metric);
            $n->prev = $n->prev->prev;
        }
    }

    private function mergeInto(Metric $into, Metric $metric): void
    {
        foreach ($metric->summaries as $k => $summary) {
            $into->attributes[$k] ??= $metric->attributes[$k];
            $into->summaries[$k] = isset($into->summaries[$k])
                ? $this->aggregation->merge($into->summaries[$k], $summary)
                : $summary;
        }
        $into->exemplars += $metric->exemplars;
    }
}
