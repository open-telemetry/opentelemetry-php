<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for error.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/error/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface ErrorIncubatingAttributes
{
    /**
     * Describes a class of error the operation ended with.
     *
     * The `error.type` SHOULD be predictable, and SHOULD have low cardinality.
     *
     * When `error.type` is set to a type (e.g., an exception type), its
     * canonical class name identifying the type within the artifact SHOULD be used.
     *
     * If the recorded error type is a wrapper that is not meaningful for
     * failure classification, instrumentation MAY use the type of the inner
     * error instead. For example, in Go, errors created with `fmt.Errorf`
     * using `%w` MAY be unwrapped when the wrapper type does not help
     * classify the failure.
     *
     * Instrumentations SHOULD document the list of errors they report.
     *
     * The cardinality of `error.type` within one instrumentation library SHOULD be low.
     * Telemetry consumers that aggregate data from multiple instrumentation libraries and applications
     * should be prepared for `error.type` to have high cardinality at query time when no
     * additional filters are applied.
     *
     * If the operation has completed successfully, instrumentations SHOULD NOT set `error.type`.
     *
     * If a specific domain defines its own set of error identifiers (such as HTTP or RPC status codes),
     * it's RECOMMENDED to:
     *
     * - Use a domain-specific attribute
     * - Set `error.type` to capture all errors, regardless of whether they are defined within the domain-specific set or not.
     *
     * @stable
     */
    public const ERROR_TYPE = 'error.type';

    /**
     * A fallback error value to be used when the instrumentation doesn't define a custom value.
     *
     * @stable
     */
    public const ERROR_TYPE_VALUE_OTHER = '_OTHER';

    /**
     * Deprecated. Use a domain-specific error message attribute instead.
     * For feature flag errors use {@see \OpenTelemetry\SemConv\Incubating\Attributes\FeatureFlagIncubatingAttributes::FEATURE_FLAG_ERROR_MESSAGE}.
     *
     * @deprecated
     */
    public const ERROR_MESSAGE = 'error.message';
}
