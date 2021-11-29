<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

final class SpanKind
{
    public const KIND_SERVER = 'SERVER';
    public const KIND_CLIENT = 'CLIENT';
    public const KIND_PRODUCER = 'PRODUCER';
    public const KIND_CONSUMER = 'CONSUMER';

    public static function getChoices(): array
    {
        return [
            self::KIND_CLIENT,
            self::KIND_SERVER,
            self::KIND_PRODUCER,
            self::KIND_CONSUMER,
        ];
    }

    private function __construct()
    {
    }
}
