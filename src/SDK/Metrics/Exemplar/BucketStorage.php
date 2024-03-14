<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Exemplar;

use function array_fill;
use function assert;
use function count;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;

/**
 * @internal
 */
final class BucketStorage
{
    /** @var array<int, BucketEntry|null> */
    private array $buckets;

    public function __construct(int $size = 0)
    {
        $this->buckets = array_fill(0, $size, null);
    }

    public function store(int $bucket, int|string $index, float|int $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): void
    {
        assert($bucket <= count($this->buckets));

        $exemplar = $this->buckets[$bucket] ??= new BucketEntry();
        $exemplar->index = $index;
        $exemplar->value = $value;
        $exemplar->timestamp = $timestamp;
        $exemplar->attributes = $attributes;

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
     * @return array<Exemplar>
     */
    public function collect(array $dataPointAttributes): array
    {
        $exemplars = [];
        foreach ($this->buckets as $index => &$exemplar) {
            if (!$exemplar) {
                continue;
            }

            $exemplars[$index] = new Exemplar(
                $exemplar->index,
                $exemplar->value,
                $exemplar->timestamp,
                $this->filterExemplarAttributes(
                    $dataPointAttributes[$exemplar->index],
                    $exemplar->attributes,
                ),
                $exemplar->traceId,
                $exemplar->spanId,
            );
            $exemplar = null;
        }

        return $exemplars;
    }

    private function filterExemplarAttributes(AttributesInterface $dataPointAttributes, AttributesInterface $exemplarAttributes): AttributesInterface
    {
        $attributes = [];
        foreach ($exemplarAttributes as $key => $value) {
            if ($dataPointAttributes->get($key) === null) {
                $attributes[$key] = $value;
            }
        }

        return new Attributes($attributes, $exemplarAttributes->getDroppedAttributesCount());
    }
}
