<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface SpanStatus
{
    const UNSET = 'Unset';
    const OK = 'Ok';
    const ERROR = 'Error';

    const DESCRIPTION = [
        self::UNSET            => 'The default unset status.',
        self::OK            => 'Not an error; returned on success.',
        self::ERROR            => 'The operation contains an error.',
    ];

    public function getCanonicalStatusCode(): string;
    public function getStatusDescription(): string;
    public function isStatusOk(): bool;
//    public function setStatus(string $code = self::UNSET, string $description = null): void;
}
