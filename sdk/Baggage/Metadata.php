<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Baggage;

use OpenTelemetry\Baggage as API;

class Metadata implements API\Metadata
{
    /** @var self|null */
    private static $instance;

    public static function getEmpty(): ?Metadata
    {
        if (null === self::$instance) {
            self::$instance = new self('');
        }

        return self::$instance;
    }

    /** @var string */
    private $metadata;

    public function __construct(string $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getMetadata(): string
    {
        return $this->metadata;
    }
}
