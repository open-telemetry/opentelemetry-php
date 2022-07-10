<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Otlp;

use OpenTelemetry\API\Common\Signal\Signals;
use Psr\Http\Message\UriInterface;

interface HttpEndpointResolverInterface
{
    public const TRACE_DEFAULT_PATH = 'v1/traces';
    public const METRICS_DEFAULT_PATH = 'v1/traces';
    public const LOGS_DEFAULT_PATH = 'v1/traces';
    public const DEFAULT_PATHS = [
        SIGNALS::TRACE => self::TRACE_DEFAULT_PATH,
        SIGNALS::METRICS => self::METRICS_DEFAULT_PATH,
        SIGNALS::LOGS => self::LOGS_DEFAULT_PATH,
    ];
    public const VALID_SCHEMES = [
        'http',
        'https',
    ];

    public function resolve(string $endpoint, string $signal): UriInterface;

    public function resolveToString(string $endpoint, string $signal): string;
}
