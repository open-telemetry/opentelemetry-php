<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Attributes;

/**
 * Semantic attributes and corresponding values for service.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/service/
 */
interface ServiceAttributes
{
    /**
     * Logical name of the service.
     *
     * MUST be the same for all instances of horizontally scaled services. If the value was not specified, SDKs MUST fallback to `unknown_service:` concatenated with [`process.executable.name`](process.md), e.g. `unknown_service:bash`. If `process.executable.name` is not available, the value MUST be set to `unknown_service`.
     *
     * @stable
     */
    public const SERVICE_NAME = 'service.name';

    /**
     * The version string of the service component. The format is not defined by these conventions.
     *
     * @stable
     */
    public const SERVICE_VERSION = 'service.version';

}
