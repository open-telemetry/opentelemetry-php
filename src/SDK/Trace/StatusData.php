<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;

final class StatusData implements StatusDataInterface
{
    private static ?self $ok = null;
    private static ?self $unset = null;
    private static ?self $error = null;

    /** @psalm-param API\StatusCode::STATUS_* $code */
    public function __construct(
        private readonly string $code,
        private readonly string $description,
    ) {
    }

    /** @psalm-param API\StatusCode::STATUS_* $code */
    public static function create(string $code, ?string $description = null): self
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

        // Ignore description for non Error statuses.
        if (API\StatusCode::STATUS_ERROR !== $code) {
            $description = '';
        }

        return new self($code, $description); /** @phan-suppress-current-line PhanTypeMismatchArgumentNullable */
    }

    #[\Override]
    public static function ok(): self
    {
        if (null === self::$ok) {
            self::$ok = new self(API\StatusCode::STATUS_OK, '');
        }

        return self::$ok;
    }

    #[\Override]
    public static function error(): self
    {
        if (null === self::$error) {
            self::$error = new self(API\StatusCode::STATUS_ERROR, '');
        }

        return self::$error;
    }

    #[\Override]
    public static function unset(): self
    {
        if (null === self::$unset) {
            self::$unset = new self(API\StatusCode::STATUS_UNSET, '');
        }

        return self::$unset;
    }

    #[\Override]
    public function getCode(): string
    {
        return $this->code;
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }
}
