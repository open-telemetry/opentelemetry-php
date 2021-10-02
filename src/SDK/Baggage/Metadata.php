<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Baggage;

use OpenTelemetry\API\Baggage as API;

final class Metadata implements API\MetadataInterface
{
    /** @var self|null */
    private static $instance;

    public static function getEmpty(): Metadata
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

    public function getValue(): string
    {
        return $this->metadata;
    }
}
