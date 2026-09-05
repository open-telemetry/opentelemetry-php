<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Attributes;

/**
 * Semantic attributes and corresponding values for exception.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/exception/
 */
interface ExceptionAttributes
{
    /**
     * The exception message.
     *
     * > [!WARNING]> This attribute may contain sensitive information.
     *
     * @stable
     */
    public const EXCEPTION_MESSAGE = 'exception.message';

    /**
     * A stacktrace as a string in the natural representation for the language runtime. The representation is to be determined and documented by each language SIG.
     *
     * @stable
     */
    public const EXCEPTION_STACKTRACE = 'exception.stacktrace';

    /**
     * The type of the exception (its fully-qualified class name, if applicable). The dynamic type of the exception should be preferred over the static type in languages that support it.
     *
     * If the recorded exception type is a wrapper that is not meaningful for
     * failure classification, instrumentation MAY use the type of the inner
     * exception instead. For example, in Go, errors created with `fmt.Errorf`
     * using `%w` MAY be unwrapped when the wrapper type does not help
     * classify the failure.
     *
     * @stable
     */
    public const EXCEPTION_TYPE = 'exception.type';

}
