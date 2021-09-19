<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

final class StatusData
{
    private static ?self $ok;
    private static ?self $unset;
    private static ?self $error;

    /** @param API\StatusCode::STATUS_* $code */
    public static function create(string $code, string $description = null): self
    {
        if (empty($description)) {
            switch ($code) {
                case API\StatusCode::STATUS_UNSET:
                    return self::unset();
                case API\StatusCode::STATUS_ERROR:
                    return self::error();
                case API\StatusCode::STATUS_OK:
                    return self::ok();
            }
        }

        return new self($code, $description);
    }

    public static function ok(): self
    {
        if (null === self::$ok) {
            self::$ok = new self(API\StatusCode::STATUS_OK, '');
        }

        return self::$ok;
    }

    public static function error(): self
    {
        if (null === self::$error) {
            self::$error = new self(API\StatusCode::STATUS_ERROR, '');
        }

        return self::$error;
    }

    public static function unset(): self
    {
        if (null === self::$unset) {
            self::$unset = new self(API\StatusCode::STATUS_UNSET, '');
        }

        return self::$unset;
    }

    private string $code;
    private string $description;

    /** @param API\StatusCode::STATUS_* $code */
    public function __construct(
        string $code,
        string $description
    ) {
        $this->code = $code;
        $this->description = $description;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
