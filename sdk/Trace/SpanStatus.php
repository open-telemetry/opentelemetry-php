<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

final class SpanStatus implements API\SpanStatus
{
    /**
     * @var SpanStatus[]
     */
    private static $map;

    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $description;

    private function __construct(int $code, string $description = null)
    {
        $this->code = $code;
        $this->description = $description ?? self::DESCRIPTION[self::UNKNOWN];
    }

    public static function new(int $code, string $description = null): SpanStatus
    {
        if (!$description) {
            $description = self::DESCRIPTION[$code] ?? self::DESCRIPTION[self::UNKNOWN];

            return self::$map[$code] ?? self::$map[$code] = new SpanStatus($code, $description);
        }

        return new SpanStatus($code, $description);
    }

    public static function ok(): SpanStatus
    {
        return self::new(self::OK);
    }

    public function getCanonicalStatusCode(): int
    {
        return $this->code;
    }

    public function getStatusDescription(): string
    {
        return $this->description;
    }

    public function isStatusOK() : bool
    {
        return $this->code === self::OK;
    }
}
