<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for exception.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/exception/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface ExceptionIncubatingAttributes
{
    /**
     * The exception message.
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
     * @stable
     */
    public const EXCEPTION_TYPE = 'exception.type';

}
