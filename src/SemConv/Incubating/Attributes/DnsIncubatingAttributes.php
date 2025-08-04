<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for dns.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/dns/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface DnsIncubatingAttributes
{
    /**
     * The list of IPv4 or IPv6 addresses resolved during DNS lookup.
     *
     * @experimental
     */
    public const DNS_ANSWERS = 'dns.answers';

    /**
     * The name being queried.
     * If the name field contains non-printable characters (below 32 or above 126), those characters should be represented as escaped base 10 integers (\DDD). Back slashes and quotes should be escaped. Tabs, carriage returns, and line feeds should be converted to \t, \r, and \n respectively.
     *
     * @experimental
     */
    public const DNS_QUESTION_NAME = 'dns.question.name';

}
