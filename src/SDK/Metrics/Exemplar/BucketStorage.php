<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Exemplar;

use OpenTelemetry\API\Trace\AbstractSpan;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesFactory;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use function array_fill;
use function assert;
use function class_exists;
use function count;

/**
 * @internal
 */
final class BucketStorage {

    private AttributesFactory $attributes;
    /** @var list<BucketEntry|null> */
    private array $buckets;

    public function __construct(AttributesFactory $attributes, int $size = 0) {
        $this->attributes = $attributes;
        $this->buckets = array_fill(0, $size, null);
    }

    public function store(int $bucket, int|string $index, float|int $value, Attributes $attributes, Context $context, int $timestamp, int $revision): void {
        assert($bucket <= count($this->buckets));

        $exemplar = $this->buckets[$bucket] ??= new BucketEntry();
        $exemplar->index = $index;
        $exemplar->value = $value;
        $exemplar->timestamp = $timestamp;
        $exemplar->attributes = $attributes;
        $exemplar->revision = $revision;

        if (class_exists(AbstractSpan::class) && ($spanContext = AbstractSpan::fromContext($context)->getContext())->isValid()) {
            $exemplar->traceId = $spanContext->getTraceId();
            $exemplar->spanId = $spanContext->getSpanId();
        } else {
            $exemplar->traceId = null;
            $exemplar->spanId = null;
        }
    }

    /**
     * @param array<Attributes> $dataPointAttributes
     * @return array<list<Exemplar>>
     */
    public function collect(array $dataPointAttributes, int $revision, int $limit): array {
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

    private function filterExemplarAttributes(Attributes $dataPointAttributes, Attributes $exemplarAttributes): Attributes {
        $attributes = $this->attributes->builder();
        foreach ($exemplarAttributes as $key => $value) {
            if ($dataPointAttributes->get($key) === null) {
                $attributes[$key] = $value;
            }
        }

        return $attributes->build();
    }
}
