<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Signals;
use Psr\Http\Message\UriInterface;

interface HttpEndpointResolverInterface
{
    public const TRACE_DEFAULT_PATH = 'v1/traces';
    public const METRICS_DEFAULT_PATH = 'v1/metrics';
    public const LOGS_DEFAULT_PATH = 'v1/logs';
    public const DEFAULT_PATHS = [
        Signals::TRACE => self::TRACE_DEFAULT_PATH,
        Signals::METRICS => self::METRICS_DEFAULT_PATH,
        Signals::LOGS => self::LOGS_DEFAULT_PATH,
    ];
    public const VALID_SCHEMES = [
        'http',
        'https',
    ];

    public function resolve(string $endpoint, string $signal): UriInterface;

    public function resolveToString(string $endpoint, string $signal): string;
}
