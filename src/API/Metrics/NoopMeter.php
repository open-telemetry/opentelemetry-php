<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

class NoopMeter implements MeterInterface
{
    public const NAME = __CLASS__;
    public const VERSION = 'unknown';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getVersion(): string
    {
        return self::VERSION;
    }

    public function newCounter(string $name, string $description): CounterInterface
    {
        $counter = new class() implements CounterInterface {
            public string $name;
            public string $description;
            public function add(int $value): CounterInterface
            {
                return $this;
            }

            public function increment(): CounterInterface
            {
                return $this;
            }

            public function getValue(): int
            {
                return 0;
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function getDescription(): string
            {
                return $this->description;
            }

            public function getType(): int
            {
                return MetricKind::COUNTER;
            }
        };

        $counter->name = $name;
        $counter->description = $description;

        return $counter;
    }

    public function newUpDownCounter(string $name, string $description): UpDownCounterInterface
    {
        return new class() implements UpDownCounterInterface {
            public function add($increment): int
            {
                return 0;
            }
        };
    }

    public function newValueRecorder(string $name, string $description): ValueRecorderInterface
    {
        $recorder = new class() implements ValueRecorderInterface {
            public string $name;
            public string $description;

            public function getName(): string
            {
                return $this->name;
            }

            public function getDescription(): string
            {
                return $this->description;
            }
            public function getType(): int
            {
                return MetricKind::VALUE_RECORDER;
            }

            public function record(float $value): void
            {
            }

            public function getSum(): float
            {
                return 0.0;
            }

            public function getMin(): float
            {
                return 0.0;
            }

            public function getMax(): float
            {
                return 0.0;
            }

            public function getCount(): int
            {
                return 0;
            }
        };

        $recorder->name = $name;
        $recorder->description = $description;

        return $recorder;
    }
}
