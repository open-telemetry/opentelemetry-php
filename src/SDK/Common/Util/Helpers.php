<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationLibrary;

class Helpers
{
    /**
     * Generate a unique key for an instance of {@see InstrumentationLibrary}, with parameters matching those of the
     * class' constructor.
     * @param string $name
     * @param ?string $version
     * @param ?string $schemaUrl
     * @return string
     */
    public static function generateInstrumentationLibraryInstanceKey(string $name, ?string $version, ?string $schemaUrl): string
    {
        return sprintf('%s@%s %s', $name, ($version ?? 'unknown'), ($schemaUrl ?? ''));
    }
}
