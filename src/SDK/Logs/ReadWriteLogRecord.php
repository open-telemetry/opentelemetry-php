<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

class ReadWriteLogRecord extends ReadableLogRecord
{
    public function setAttribute(string $name, mixed $value): self
    {
        $this->attributesBuilder->offsetSet($name, $value);

        return $this;
    }

    public function removeAttribute(string $key): self
    {
        $this->attributesBuilder->offsetUnset($key);

        return $this;
    }
}
