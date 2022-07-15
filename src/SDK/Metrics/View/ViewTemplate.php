<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View;

use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\ViewProjection;

final class ViewTemplate
{
    private ?string $name = null;
    private ?string $description = null;
    /**
     * @var list<string>
     */
    private ?array $attributeKeys = null;
    private ?AggregationInterface $aggregation = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        static $instance;

        return $instance ??= new self();
    }

    public function withName(string $name): self
    {
        $self = clone $this;
        $self->name = $name;

        return $self;
    }

    public function withDescription(string $description): self
    {
        $self = clone $this;
        $self->description = $description;

        return $self;
    }

    /**
     * @param list<string> $attributeKeys
     */
    public function withAttributeKeys(array $attributeKeys): self
    {
        $self = clone $this;
        $self->attributeKeys = $attributeKeys;

        return $self;
    }

    public function withAggregation(?AggregationInterface $aggregation): self
    {
        $self = clone $this;
        $self->aggregation = $aggregation;

        return $self;
    }

    public function project(Instrument $instrument): ViewProjection
    {
        return new ViewProjection(
            $this->name ?? $instrument->name,
            $instrument->unit,
            $this->description ?? $instrument->description,
            $this->attributeKeys,
            $this->aggregation,
        );
    }
}
