<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Metrics;

/**
 * Metrics for dns.
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface DnsIncubatingMetrics
{
    /**
     * Measures the time taken to perform a DNS lookup.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const DNS_LOOKUP_DURATION = 'dns.lookup.duration';

}
