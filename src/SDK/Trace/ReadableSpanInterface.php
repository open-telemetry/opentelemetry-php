<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/sdk.md#additional-span-interfaces
 */
interface ReadableSpanInterface
{
    public function getName(): string;

    public function getContext(): API\SpanContextInterface;

    public function getParentContext(): API\SpanContextInterface;

    public function getInstrumentationScope(): InstrumentationScopeInterface;

    public function hasEnded(): bool;

    /**
     * Returns an immutable representation of this instance.
     */
    public function toSpanData(): SpanDataInterface;

    /**
     * Returns the duration of the {@see API\SpanInterface} in nanoseconds.
     * If still active, returns `now() - start`.
     */
    public function getDuration(): int;

    /**
     * @see API\SpanKind
     */
    public function getKind(): int;

    /**
     * Returns the value of the attribute with the provided *key*.
     * Returns `null` if there are no attributes set, or no attribute with that key exists.
     *
     * @return mixed
     */
    public function getAttribute(string $key);
}
