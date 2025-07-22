<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use function count;
use function end;
use function explode;
use function key;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use function prev;
use function sprintf;
use function strlen;
use function trim;

class TraceState implements TraceStateInterface
{
    use LogsMessagesTrait;

    public const MAX_LIST_MEMBERS = 32; //@see https://www.w3.org/TR/trace-context/#tracestate-header-field-values
    /** @deprecated will be removed */
    public const MAX_COMBINED_LENGTH = 512; //@see https://www.w3.org/TR/trace-context/#tracestate-limits
    public const LIST_MEMBERS_SEPARATOR = ',';
    public const LIST_MEMBER_KEY_VALUE_SPLITTER = '=';
    private const VALID_KEY_CHAR_RANGE = '[_0-9a-z-*\/]';
    private const VALID_KEY = '[a-z]' . self::VALID_KEY_CHAR_RANGE . '{0,255}';
    private const VALID_VENDOR_KEY = '[a-z0-9]' . self::VALID_KEY_CHAR_RANGE . '{0,240}@[a-z]' . self::VALID_KEY_CHAR_RANGE . '{0,13}';
    private const VALID_KEY_REGEX = '/^(?:' . self::VALID_KEY . '|' . self::VALID_VENDOR_KEY . ')$/';
    private const VALID_VALUE_BASE_REGEX = '/^[ -~]{0,255}[!-~]$/';
    private const INVALID_VALUE_COMMA_EQUAL_REGEX = '/,|=/';

    /** @var array<string, string> */
    private array $traceState;

    public function __construct(?string $rawTracestate = null)
    {
        $this->traceState = self::parse($rawTracestate ?? '');
    }

    #[\Override]
    public function with(string $key, string $value): TraceStateInterface
    {
        if (!self::validateMember($this->traceState, $key, $value)) {
            self::logWarning('Invalid tracestate key/value for: ' . $key);

            return $this;
        }

        $clone = clone $this;
        $clone->traceState = [$key => $value] + $this->traceState;

        return $clone;
    }

    #[\Override]
    public function without(string $key): TraceStateInterface
    {
        if (!isset($this->traceState[$key])) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->traceState[$key]);

        return $clone;
    }

    #[\Override]
    public function get(string $key): ?string
    {
        return $this->traceState[$key] ?? null;
    }

    #[\Override]
    public function getListMemberCount(): int
    {
        return count($this->traceState);
    }

    #[\Override]
    public function toString(?int $limit = null): string
    {
        $traceState = $this->traceState;

        if ($limit !== null) {
            $length = 0;
            foreach ($traceState as $key => $value) {
                $length && ($length += 1);
                $length += strlen($key) + 1 + strlen($value);
            }
            if ($length > $limit) {
                // Entries larger than 128 characters long SHOULD be removed first.
                foreach ([128, 0] as $threshold) {
                    // Then entries SHOULD be removed starting from the end of tracestate.
                    for ($value = end($traceState); $key = key($traceState);) {
                        assert($value !== false);
                        $entry = strlen($key) + 1 + strlen($value);
                        $value = prev($traceState);
                        if ($entry <= $threshold) {
                            continue;
                        }

                        unset($traceState[$key]);
                        if (($length -= $entry + 1) <= $limit) {
                            break 2;
                        }
                    }
                }
            }
        }

        $s = '';
        foreach ($traceState as $key => $value) {
            $s && ($s .= ',');
            $s .= $key;
            $s .= '=';
            $s .= $value;
        }

        return $s;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    private static function parse(string $rawTracestate): array
    {
        $traceState = [];
        $members = explode(',', $rawTracestate);
        foreach ($members as $member) {
            if (($member = trim($member, " \t")) === '') {
                continue;
            }

            $member = explode('=', $member, 2);
            if (count($member) !== 2) {
                self::logWarning(sprintf('Incomplete list member in tracestate "%s"', $rawTracestate));

                return [];
            }

            [$key, $value] = $member;
            if (!self::validateMember($traceState, $key, $value)) {
                self::logWarning(sprintf('Invalid list member "%s=%s" in tracestate "%s"', $key, $value, $rawTracestate));

                return [];
            }

            $traceState[$key] ??= $value;
        }

        return $traceState;
    }

    private static function validateMember(array $traceState, string $key, string $value): bool
    {
        return (isset($traceState[$key]) || self::validateKey($key))
            && self::validateValue($value)
            && (count($traceState) < self::MAX_LIST_MEMBERS || isset($traceState[$key]));
    }

    /**
     * The Key is opaque string that is an identifier for a vendor. It can be up
     * to 256 characters and MUST begin with a lowercase letter or a digit, and can
     * only contain lowercase letters (a-z), digits (0-9), underscores (_), dashes (-),
     * asterisks (*), and forward slashes (/). For multi-tenant vendor scenarios, an at
     * sign (@) can be used to prefix the vendor name. Vendors SHOULD set the tenant ID
     * at the beginning of the key.
     *
     * @see https://www.w3.org/TR/trace-context/#key
     */
    private static function validateKey(string $key): bool
    {
        return preg_match(self::VALID_KEY_REGEX, $key) !== 0;
    }

    /**
     * The value is an opaque string containing up to 256 printable ASCII [RFC0020]
     * characters (i.e., the range 0x20 to 0x7E) except comma (,) and (=). Note that
     * this also excludes tabs, newlines, carriage returns, etc.
     *
     * @see https://www.w3.org/TR/trace-context/#value
     */
    private static function validateValue(string $key): bool
    {
        return (preg_match(self::VALID_VALUE_BASE_REGEX, $key) !== 0)
            && (preg_match(self::INVALID_VALUE_COMMA_EQUAL_REGEX, $key) === 0);
    }
}
