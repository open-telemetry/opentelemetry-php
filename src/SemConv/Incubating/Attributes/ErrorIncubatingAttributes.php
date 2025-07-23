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
     * A message providing more detail about an error in human-readable form.
     * `error.message` should provide additional context and detail about an error.
     * It is NOT RECOMMENDED to duplicate the value of `error.type` in `error.message`.
     * It is also NOT RECOMMENDED to duplicate the value of `exception.message` in `error.message`.
     *
     * `error.message` is NOT RECOMMENDED for metrics or spans due to its unbounded cardinality and overlap with span status.
     *
     * @experimental
     */
    public const ERROR_MESSAGE = 'error.message';

    /**
     * Describes a class of error the operation ended with.
     *
     * The `error.type` SHOULD be predictable, and SHOULD have low cardinality.
     *
     * When `error.type` is set to a type (e.g., an exception type), its
     * canonical class name identifying the type within the artifact SHOULD be used.
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
     * If a specific domain defines its own set of error identifiers (such as HTTP or gRPC status codes),
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

}
