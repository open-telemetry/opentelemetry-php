<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

interface StatusDataInterface
{
    public static function ok(): self;

    public static function error(): self;

    public static function unset(): self;

    public function getCode(): string;

    public function getDescription(): string;
}
