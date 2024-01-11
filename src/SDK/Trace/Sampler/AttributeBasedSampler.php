<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;

/**
 * @phan-file-suppress PhanParamTooFewUnpack
 *
 * Attribute-based sampler for root spans.
 * "allow" mode: only sample root span if attribute exists and matches the pattern, else do not sample
 * "deny" mode: do not sample root span if attribute exists and matches the pattern, else defer to next sampler
 */
class AttributeBasedSampler implements SamplerInterface
{
    use LogsMessagesTrait;

    public const ALLOW = 'allow';
    public const DENY = 'deny';
    public const MODES = [
        self::ALLOW,
        self::DENY,
    ];

    private SamplerInterface $delegate;
    private string $mode;
    private string $attribute;
    private string $pattern;

    /**
     * @param SamplerInterface $delegate The sampler to defer to if a decision is not made by this sampler
     * @param string $mode Sampling mode (deny or allow)
     * @param string $attribute The SemConv trace attribute to test against, eg http.path, http.method
     * @param string $pattern The PCRE regex pattern to match against, eg /\/health$|\/test$/
     */
    public function __construct(SamplerInterface $delegate, string $mode, string $attribute, string $pattern)
    {
        if (!in_array($mode, self::MODES)) {
            throw new InvalidArgumentException('Unknown Attribute sampler mode: ' . $mode);
        }
        $this->delegate = $delegate;
        $this->mode = $mode;
        $this->attribute = $attribute;
        $this->pattern = $pattern;
    }

    public function shouldSample(ContextInterface $parentContext, string $traceId, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingResult
    {
        switch ($this->mode) {
            case self::ALLOW:
                if (!$attributes->has($this->attribute)) {
                    return new SamplingResult(SamplingResult::DROP);
                }
                if ($this->matches((string) $attributes->get($this->attribute))) {
                    return new SamplingResult(SamplingResult::RECORD_AND_SAMPLE);
                }

                break;
            case self::DENY:
                if (!$attributes->has($this->attribute)) {
                    break;
                }
                if ($this->matches((string) $attributes->get($this->attribute))) {
                    return new SamplingResult(SamplingResult::DROP);
                }

                break;
            default:
                //do nothing
        }

        return $this->delegate->shouldSample(...func_get_args());
    }

    /**
     * @todo call preg_last_error_msg directly after 7.4 support dropped
     * @phan-suppress PhanUndeclaredFunctionInCallable
     */
    private function matches(string $value): bool
    {
        $result = @preg_match($this->pattern, $value);
        if ($result === false) {
            self::logWarning('Error when pattern matching attribute', [
                'attribute.name' => $this->attribute,
                'attribute.value' => $value,
                'pattern' => $this->pattern,
                'error' => function_exists('preg_last_error_msg') ? call_user_func('preg_last_error_msg') : '',
            ]);

            return false;
        }

        return (bool) $result;
    }

    public function getDescription(): string
    {
        return sprintf('AttributeSampler{mode=%s,attribute=%s,pattern=%s}+%s', $this->mode, $this->attribute, $this->pattern, $this->delegate->getDescription());
    }
}
