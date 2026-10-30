<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use function assert;
use function bin2hex;
use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\OtelTraceState;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;
use function pack;
use function rtrim;
use function sprintf;
use function substr;
use function substr_compare;
use function unpack;

/**
 * This implementation of the SamplerInterface records with given probability.
 * Example:
 * ```
 * use OpenTelemetry\API\Trace\TraceIdRatioBasedSampler;
 * $sampler = new TraceIdRatioBasedSampler(0.01);
 * ```
 */
class TraceIdRatioBasedSampler implements SamplerInterface
{
    use LogsMessagesTrait;

    private readonly float $probability;
    private readonly string $tv;
    private bool $warnedAsChild = false;

    /**
     * @param float $probability Probability float value between 0.0 and 1.0.
     * @param int<1, 14> $precision threshold precision in hexadecimal digits
     */
    public function __construct(float $probability, int $precision = 4)
    {
        if (!($probability >= 0 && $probability <= 1)) {
            throw new InvalidArgumentException('probability should be be between 0.0 and 1.0.');
        }

        $this->probability = $probability;
        $this->tv = rtrim(bin2hex(substr(pack('J', self::computeTValue($probability, $precision, 4)), 1)), '0') ?: '0';
    }

    /**
     * Warns once per instance when used as a child sampler.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/trace/sdk.md#compatibility-warnings-for-traceidratiobased-sampler
     */
    #[\Override]
    public function shouldSample(
        ContextInterface $parentContext,
        string $traceId,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingResult {
        $parentSpanContext = Span::fromContext($parentContext)->getContext();
        $traceState = $parentSpanContext->getTraceState();

        if (!$this->warnedAsChild && $parentSpanContext->isValid()) {
            $this->warnedAsChild = true;
            $otelTraceState = OtelTraceState::parse($traceState?->get(OtelTraceState::TRACE_STATE_KEY));
            self::logWarning($otelTraceState->hasValidThreshold() || $otelTraceState->hasValidRandomValue()
                ? 'The TraceIdRatioBased sampler is operating as a child sampler and a parent is using ProbabilitySampler. Please upgrade this SDK configuration to use ProbabilitySampler.'
                : 'The TraceIdRatioBased sampler is operating as a child sampler; the behavior is subject to change. Please upgrade this SDK configuration to use ProbabilitySampler.');
        }

        $decision = $this->probability >= 2 ** -56 && substr_compare($traceId, $this->tv, -14) >= 0
            ? SamplingResult::RECORD_AND_SAMPLE
            : SamplingResult::DROP;

        return new SamplingResult($decision, [], $traceState);
    }

    /**
     * Computes the 56-bit rejection threshold (T-value) for a given probability.
     *
     * The T-value is computed as `2**56*(1-$probability)` with a precision of
     * `2**-($wordSize*⌈-log2($probability)/$wordSize+$precision-1⌉)`.
     *
     * Values below `2**-56` will return `0`.
     *
     * ```
     * 1/3 w/ precision=3, wordSize=4
     * => 1 - 1/3
     * => 2/3
     * => 2730.666../4096
     * => 2731/4096
     * => 0xaab
     * ```
     *
     * Converting the result into `th` hexadecimal value:
     * ```
     * $th = rtrim(bin2hex(substr(pack('J', $t), 1)), '0') ?: '0';
     * ```
     *
     * @param float $probability sampling probability, must be between 0 and 1
     * @param int $precision precision in words
     * @param int $wordSize word size to use, must be a power of two
     * @return int 56bit T-value
     *
     * @internal
     */
    public static function computeTValue(float $probability, int $precision, int $wordSize = 1): int
    {
        assert($probability >= 0 && $probability <= 1);
        assert($precision >= 1);
        assert($wordSize >= 1 && ($wordSize & $wordSize - 1) === 0);

        /** @psalm-suppress PossiblyInvalidArrayAccess */
        $b = unpack('J', pack('E', $probability))[1];
        $e = $b >> 52 & (1 << 11) - 1;
        $f = $b & (1 << 52) - 1 | ($e ? 1 << 52 : 0);

        // 56+1bit for rounding
        $s = $e - 1023 - 52 + 57;
        $t = (1 << 57) - ($s < 0 ? $f >> -$s : $f << $s);
        $m = -1 << 56 >> (-($e - 1023 + 1) + $precision * $wordSize & -$wordSize);

        return $t - $m >> 1 & $m;
    }

    #[\Override]
    public function getDescription(): string
    {
        return sprintf('TraceIdRatioBased{%.6F}', $this->probability);
    }
}
