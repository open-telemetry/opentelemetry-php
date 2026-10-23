<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View;

use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\ViewProjection;

final class ViewTemplate
{
    private ?string $name = null;
    private ?string $description = null;
    /** @var list<string> */
    private ?array $attributeKeys = null;
    private ?AggregationInterface $aggregation = null;
    private ?DefaultAggregationProviderInterface $aggregationResolver = null;

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
        $self->aggregationResolver = null;

        return $self;
    }

    /**
     * Resolves the aggregation per instrument, from its type and advisory parameters. A null result
     * means the reader's default aggregation.
     */
    public function withAggregationResolver(DefaultAggregationProviderInterface $aggregationResolver): self
    {
        $self = clone $this;
        $self->aggregation = null;
        $self->aggregationResolver = $aggregationResolver;

        return $self;
    }

    /**
     * @psalm-suppress TooManyArguments
     */
    public function project(Instrument $instrument): ViewProjection
    {
        $aggregation = $this->aggregation;
        if ($this->aggregationResolver !== null) {
            /** @phan-suppress-next-line PhanParamTooMany @phpstan-ignore-next-line */
            $aggregation = $this->aggregationResolver->defaultAggregation($instrument->type, $instrument->advisory);
        }

        return new ViewProjection(
            $this->name ?? $instrument->name,
            $instrument->unit,
            $this->description ?? $instrument->description,
            $this->attributeKeys,
            $aggregation,
        );
    }
}
