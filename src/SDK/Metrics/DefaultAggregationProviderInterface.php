<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface DefaultAggregationProviderInterface
{
    /**
     * @param string|InstrumentType $instrumentType
     * @param array $advisory optional set of recommendations
     *
     * @noinspection PhpDocSignatureInspection not added for BC
     * @phan-suppress PhanCommentParamWithoutRealParam @phpstan-ignore-next-line
     */
    public function defaultAggregation($instrumentType /*, array $advisory = [] */): ?AggregationInterface;
}
