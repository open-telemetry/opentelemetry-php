<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Stream;

use GMP;
use OpenTelemetry\SDK\Metrics\Aggregation;

final class DeltaStorage {

    private Aggregation $aggregation;
    private Delta $head;

    public function __construct(Aggregation $aggregation) {
        $this->aggregation = $aggregation;
        $this->head = new Delta(new Metric(), 0, 0);
    }

    public function add(Metric $metric, int|GMP $readers, int $timestamp): void {
        if ($readers == 0) {
            return;
        }

        if ($this->head->prev?->readers != $readers) {
            $this->head->prev = new Delta($metric, $readers, $timestamp, $this->head->prev);
        } else {
            $this->mergeInto($this->head->prev->metric, $metric);
        }
    }

    public function collect(int $reader, bool $retain = false): ?Delta {
        $n = null;
        for ($d = $this->head; $d->prev; $d = $d->prev) {
            if (($d->prev->readers >> $reader & 1) != 0) {
                if ($n) {
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

        $delta = $n?->prev;

        if (!$retain && $n) {
            $n->prev->readers ^= ($n->prev->readers & 1 | 1) << $reader;
            $this->tryUnlink($n);
        }

        return $delta;
    }

    private function tryUnlink(Delta $n): void {
        if ($n->prev->readers == 0) {
            $n->prev = $n->prev->prev;
            return;
        }

        for ($c = $n->prev->prev;
             $c && ($n->prev->readers & $c->readers) == 0;
             $c = $c->prev) {}

        if ($n->prev->readers == $c?->readers) {
            $this->mergeInto($c->metric, $n->prev->metric);
            $n->prev = $n->prev->prev;
        }
    }

    private function mergeInto(Metric $into, Metric $metric): void {
        foreach ($metric->summaries as $k => $summary) {
            $into->attributes[$k] ??= $metric->attributes[$k];
            $into->summaries[$k] = isset($into->summaries[$k])
                ? $this->aggregation->merge($into->summaries[$k], $summary)
                : $summary;
        }
    }
}
