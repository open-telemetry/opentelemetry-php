<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;

interface LogRecordInterface
{
    public const NANOS_PER_SECOND = 1_000_000_000;

    public function setBody($body): self;
    public function setTimestamp(int $timestamp): self;
    public function setObservedTimestamp(int $observedTimestamp): self;
    public function setContext(?ContextInterface $context): self;
    public function setSeverityNumber(int $severityNumber): self;
    public function setSeverityText(string $severityText): self;
    public function setAttributes(iterable $attributes): self;
    public function setAttribute(string $name, $value): self;
}
