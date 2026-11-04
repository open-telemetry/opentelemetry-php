<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use function is_array;
use function is_string;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Util\WildcardMatcher;
use function str_starts_with;
use function strpos;
use function strtolower;
use function strtr;
use function substr;
use function trim;

/**
 * Decides whether a request should be ignored because of its headers, as configured by
 * OTEL_PHP_EXCLUDED_REQUEST_HEADERS.
 *
 * Each entry is `name: value` or just `name`. The name is case-insensitive and `_` is the same as `-`, as in $_SERVER. The value accepts `*` wildcards and is
 * case-insensitive unless it starts with `(?-i)`. An entry without a value (`name` or `name:`) matches whenever the
 * header is present, a rule for an empty value cannot be written.
 *
 * The configuration is a comma-separated list, as everywhere in the SDK, so a value cannot contain a comma and there
 * is no escaping. A header sent more than once reaches the matcher as one value joined with `, ` by the SAPI, so use
 * wildcards to match one of them (`*b*`).
 *
 * The object holds no state beyond the parsed rules and {@see self::fromConfiguration()} reads the configuration on
 * every call, so a value changed at runtime (for example pushed by a central configuration) applies to the next
 * request, as long as it is made available through the usual configuration sources before the root span starts.
 *
 * Code in this file is part of implementation internals and thus it is not covered by the backward compatibility.
 *
 * @internal
 */
final class ExcludedRequestHeaders
{
    /** @var array<string, list<WildcardMatcher|null>> lowercase header name => value matchers, null for "any value" */
    private array $rules = [];

    /**
     * @param iterable<string> $entries
     */
    public function __construct(iterable $entries)
    {
        foreach ($entries as $entry) {
            $colon = strpos($entry, ':');
            $name = strtr(strtolower(trim($colon === false ? $entry : substr($entry, 0, $colon))), '_', '-');
            if ($name === '') {
                continue;
            }
            $value = $colon === false ? '' : trim(substr($entry, $colon + 1));
            $this->rules[$name][] = $value === '' ? null : new WildcardMatcher($value);
        }
    }

    /**
     * Request headers as found in $_SERVER (`HTTP_X_SKIP_ME` is `x-skip-me`), for use before a PSR-7 request exists.
     *
     * @param array<array-key, mixed> $server
     * @return array<string, string>
     */
    public static function headersFromServer(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            $key = (string) $key;
            if (!is_string($value)) {
                continue;
            }
            if (str_starts_with($key, 'HTTP_')) {
                $headers[strtr(strtolower(substr($key, 5)), '_', '-')] = $value;
            } elseif ($key === 'CONTENT_TYPE' || $key === 'CONTENT_LENGTH') {
                $headers[strtr(strtolower($key), '_', '-')] = $value;
            }
        }

        return $headers;
    }

    public static function fromConfiguration(): self
    {
        return new self(Configuration::getList(Variables::OTEL_PHP_EXCLUDED_REQUEST_HEADERS, []));
    }

    /**
     * @param array<array-key, string|string[]> $headers header name => value(s), as returned by PSR-7 getHeaders()
     */
    public function matches(array $headers): bool
    {
        if ($this->rules === []) {
            return false;
        }
        foreach ($headers as $name => $values) {
            $matchers = $this->rules[strtr(strtolower((string) $name), '_', '-')] ?? null;
            if ($matchers === null) {
                continue;
            }
            foreach ($matchers as $matcher) {
                if ($matcher === null) {
                    return true;
                }
                foreach (is_array($values) ? $values : [$values] as $value) {
                    if ($matcher->match($value)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
