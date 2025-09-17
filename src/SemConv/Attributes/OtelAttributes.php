<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Attributes;

/**
 * Semantic attributes and corresponding values for otel.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/otel/
 */
interface OtelAttributes
{
    /**
     * The name of the instrumentation scope - (`InstrumentationScope.Name` in OTLP).
     *
     * @stable
     */
    public const OTEL_SCOPE_NAME = 'otel.scope.name';

    /**
     * The version of the instrumentation scope - (`InstrumentationScope.Version` in OTLP).
     *
     * @stable
     */
    public const OTEL_SCOPE_VERSION = 'otel.scope.version';

    /**
     * Name of the code, either "OK" or "ERROR". MUST NOT be set if the status code is UNSET.
     *
     * @stable
     */
    public const OTEL_STATUS_CODE = 'otel.status_code';

    /**
     * The operation has been validated by an Application developer or Operator to have completed successfully.
     * @stable
     */
    public const OTEL_STATUS_CODE_VALUE_OK = 'OK';

    /**
     * The operation contains an error.
     * @stable
     */
    public const OTEL_STATUS_CODE_VALUE_ERROR = 'ERROR';

    /**
     * Description of the Status if it has a value, otherwise not set.
     *
     * @stable
     */
    public const OTEL_STATUS_DESCRIPTION = 'otel.status_description';

}
