<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

/**
 * Marker/interface equivalents for the Java types referenced by ConsistentSampler.
 * These are included so the converted code is complete and functional.
 */
interface Sampler
{
}

interface Composable
{
}

interface PredicatedSampler
{
}

interface SpanKind
{
}

/**
 * Equivalent to the Java @Nullable annotation in this context.
 * Kept as PHPDoc only; PHP does not require a special type for nullability here.
 */

/**
 * Base class for consistent samplers.
 */
abstract class ConsistentSampler implements Sampler, Composable
{
    /**
     * Returns a ConsistentSampler that samples all spans.
     *
     * @return ConsistentSampler a sampler
     */
    public static function alwaysOn(): ConsistentSampler
    {
        return ConsistentAlwaysOnSampler::getInstance();
    }

    /**
     * Returns a ConsistentSampler that does not sample any span.
     *
     * @return ConsistentSampler a sampler
     */
    public static function alwaysOff(): ConsistentSampler
    {
        return ConsistentAlwaysOffSampler::getInstance();
    }

    /**
     * Returns a ConsistentSampler that samples each span with a fixed probability.
     *
     * @param float $samplingProbability the sampling probability
     * @return ConsistentSampler a sampler
     */
    public static function probabilityBased(float $samplingProbability): ConsistentSampler
    {
        $threshold = ConsistentSamplingUtil::calculateThreshold($samplingProbability);
        return new ConsistentFixedThresholdSampler($threshold);
    }

    /**
     * Returns a new ConsistentSampler that respects the sampling decision of the parent span
     * or falls-back to the given sampler if it is a root span.
     *
     * @param Composable $rootSampler the root sampler
     * @return ConsistentSampler
     */
    public static function parentBased(Composable $rootSampler): ConsistentSampler
    {
        return new ConsistentParentBasedSampler($rootSampler);
    }

    /**
     * Constructs a new consistent rule based sampler using the given sequence of Predicates and
     * delegate Samplers.
     *
     * @param SpanKind|null $spanKindToMatch the SpanKind for which the Sampler applies, null value indicates all
     *     SpanKinds
     * @param PredicatedSampler ...$samplers the PredicatedSamplers to evaluate and query
     * @return ConsistentSampler
     */
    public static function ruleBased(?SpanKind $spanKindToMatch, PredicatedSampler ...$samplers): ConsistentSampler
    {
        return new ConsistentRuleBasedSampler($spanKindToMatch, $samplers);
    }
}

/**
 * Returns a fixed singleton sampler that samples all spans.
 */
final class ConsistentAlwaysOnSampler extends ConsistentSampler
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

/**
 * Returns a fixed singleton sampler that samples no spans.
 */
final class ConsistentAlwaysOffSampler extends ConsistentSampler
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

/**
 * Utility for consistent sampling calculations.
 */
final class ConsistentSamplingUtil
{
    /**
     * Calculate a threshold from a probability in the range [0.0, 1.0].
     * Uses a 64-bit unsigned space mapped into PHP's signed integer range as best as possible.
     *
     * @param float $samplingProbability
     * @return int
     */
    public static function calculateThreshold(float $samplingProbability): int
    {
        if (is_nan($samplingProbability)) {
            throw new InvalidArgumentException('samplingProbability must not be NaN.');
        }

        if ($samplingProbability <= 0.0) {
            return PHP_INT_MAX;
        }

        if ($samplingProbability >= 1.0) {
            return 0;
        }

        // Higher probability => lower threshold.
        return (int) floor((1.0 - $samplingProbability) * PHP_INT_MAX);
    }
}

/**
 * Sampler based on a fixed threshold.
 */
final class ConsistentFixedThresholdSampler extends ConsistentSampler
{
    private int $threshold;

    public function __construct(int $threshold)
    {
        $this->threshold = $threshold;
    }

    public function getThreshold(): int
    {
        return $this->threshold;
    }
}

/**
 * Sampler that respects the parent span decision, falling back to a root sampler.
 */
final class ConsistentParentBasedSampler extends ConsistentSampler
{
    private Composable $rootSampler;

    public function __construct(Composable $rootSampler)
    {
        $this->rootSampler = $rootSampler;
    }

    public function getRootSampler(): Composable
    {
        return $this->rootSampler;
    }
}

/**
 * Rule-based sampler using a target SpanKind and a sequence of predicated samplers.
 */
final class ConsistentRuleBasedSampler extends ConsistentSampler
{
    private ?SpanKind $spanKindToMatch;

    /** @var PredicatedSampler[] */
    private array $samplers;

    /**
     * @param SpanKind|null $spanKindToMatch
     * @param PredicatedSampler[] $samplers
     */
    public function __construct(?SpanKind $spanKindToMatch, array $samplers)
    {
        foreach ($samplers as $sampler) {
            if (!$sampler instanceof PredicatedSampler) {
                throw new InvalidArgumentException('All items in $samplers must implement PredicatedSampler.');
            }
        }

        $this->spanKindToMatch = $spanKindToMatch;
        $this->samplers = array_values($samplers);
    }

    public function getSpanKindToMatch(): ?SpanKind
    {
        return $this->spanKindToMatch;
    }

    /**
     * @return PredicatedSampler[]
     */
    public function getSamplers(): array
    {
        return $this->samplers;
    }
}
