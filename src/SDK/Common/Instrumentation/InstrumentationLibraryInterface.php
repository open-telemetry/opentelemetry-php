<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Instrumentation;

interface InstrumentationLibraryInterface
{
    public function getName(): string;

    public function getVersion(): ?string;

    public function getSchemaUrl(): ?string;
}
