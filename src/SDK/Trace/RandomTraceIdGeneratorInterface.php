<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

/**
 * Marks an ID generator whose trace IDs meet the W3C Trace Context Level 2 randomness
 * requirements, so that root spans get the `Random` trace flag.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/trace/sdk.md#idgenerator-randomness-1
 * @see https://www.w3.org/TR/trace-context-2/#randomness-of-trace-id
 */
interface RandomTraceIdGeneratorInterface extends IdGeneratorInterface
{
}
