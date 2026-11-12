<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

/**
 * Changes are visible to the processors registered after the one that makes them.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/sdk.md#readwritelogrecord
 */
class ReadWriteLogRecord extends ReadableLogRecord
{
    /**
     * The value is subject to the attribute limits. A null value removes the attribute.
     */
    #[\Override]
    public function setAttribute(string $name, mixed $value): self
    {
        $this->attributesBuilder[$name] = $value;
        $this->convertedAttributes = $this->attributesBuilder->build();

        return $this;
    }

    public function removeAttribute(string $name): self
    {
        unset($this->attributesBuilder[$name]);
        $this->convertedAttributes = $this->attributesBuilder->build();

        return $this;
    }
}
