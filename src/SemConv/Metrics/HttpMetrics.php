<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Metrics;

/**
 * Metrics for http.
 */
interface HttpMetrics
{
    /**
     * Duration of HTTP client requests.
     *
     * Instrument: histogram
     * Unit: s
     * @stable
     */
    public const HTTP_CLIENT_REQUEST_DURATION = 'http.client.request.duration';

    /**
     * Duration of HTTP server requests.
     *
     * Instrument: histogram
     * Unit: s
     * @stable
     */
    public const HTTP_SERVER_REQUEST_DURATION = 'http.server.request.duration';

}
