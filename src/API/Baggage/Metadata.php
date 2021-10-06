<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage;

final class Metadata implements MetadataInterface
{
    private static ?self $instance = null;

    public static function getEmpty(): Metadata
    {
        if (null === self::$instance) {
            self::$instance = new self('');
        }

        return self::$instance;
    }

    private string $metadata;

    public function __construct(string $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getValue(): string
    {
        return $this->metadata;
    }
}
