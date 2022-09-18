<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use function array_fill;
use function assert;
use function count;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;

/**
 * @internal
 */
final class BucketStorage
{
    private AttributesFactoryInterface $attributesFactory;
    /** @var array<int, BucketEntry|null> */
    private array $buckets;

    public function __construct(AttributesFactoryInterface $attributesFactory, int $size = 0)
    {
        $this->attributesFactory = $attributesFactory;
        $this->buckets = array_fill(0, $size, null);
    }

    /**
     * @param int|string $index
     * @param float|int $value
     */
    public function store(int $bucket, $index, $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp, int $revision): void
    {
        assert($bucket <= count($this->buckets));

        $exemplar = $this->buckets[$bucket] ??= new BucketEntry();
        $exemplar->index = $index;
        $exemplar->value = $value;
        $exemplar->timestamp = $timestamp;
        $exemplar->attributes = $attributes;
        $exemplar->revision = $revision;

        if (($spanContext = Span::fromContext($context)->getContext())->isValid()) {
            $exemplar->traceId = $spanContext->getTraceId();
            $exemplar->spanId = $spanContext->getSpanId();
        } else {
            $exemplar->traceId = null;
            $exemplar->spanId = null;
        }
    }

    /**
     * @param array<AttributesInterface> $dataPointAttributes
     * @return array<list<Exemplar>>
     */
    public function collect(array $dataPointAttributes, int $revision, int $limit): array
    {
        $exemplars = [];
        foreach ($this->buckets as $exemplar) {
            if (!$exemplar || $exemplar->revision < $revision || $exemplar->revision >= $limit) {
                continue;
            }

            $exemplars[$exemplar->index][] = new Exemplar(
                $exemplar->value,
                $exemplar->timestamp,
                $this->filterExemplarAttributes(
                    $dataPointAttributes[$exemplar->index],
                    $exemplar->attributes,
                ),
                $exemplar->traceId,
                $exemplar->spanId,
            );
        }

        return $exemplars;
    }

    private function filterExemplarAttributes(AttributesInterface $dataPointAttributes, AttributesInterface $exemplarAttributes): AttributesInterface
    {
        $attributes = $this->attributesFactory->builder();
        foreach ($exemplarAttributes as $key => $value) {
            if ($dataPointAttributes->get($key) === null) {
                $attributes[$key] = $value;
            }
        }

        return $attributes->build();
    }
}
