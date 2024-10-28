<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

interface TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface;
    public function type(): string;
    public function priority(): int;
}