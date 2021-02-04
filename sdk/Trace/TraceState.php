<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

class TraceState implements API\TraceState
{
    public const MAX_TRACESTATE_LIST_MEMBERS = 32;
    public const MAX_TRACESTATE_LENGTH = 512;
    public const LIST_MEMBERS_SEPARATOR = ',';
    public const LIST_MEMBER_KEY_VALUE_SPLITTER = '=';
    private const VALID_KEY_CHAR_RANGE = '[_0-9a-z-*\/]';
    private const VALID_KEY = '[a-z]' . self::VALID_KEY_CHAR_RANGE . '{0,255}';
    private const VALID_VENDOR_KEY = '[a-z0-9]' . self::VALID_KEY_CHAR_RANGE . '{0,240}@[a-z]' . self::VALID_KEY_CHAR_RANGE . '{0,13}';
    private const VALID_KEY_REGEX = '/^(?:' . self::VALID_KEY . '|' . self::VALID_VENDOR_KEY . ')$/';
    private const VALID_VALUE_BASE_REGEX = '/^[ -~]{0,255}[!-~]$/';
    private const INVALID_VALUE_COMMA_EQUAL_REGEX = '/,|=/';

    /**
     * @var string[]
     */
    private $traceState = [];

    public function __construct(string $rawTracestate = null)
    {
        if ($rawTracestate != null) {
            $this->traceState = self::parse($rawTracestate);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function with(string $key, string $value): API\TraceState
    {
        $clonedTracestate = clone $this;

        //TODO: Log if we can't set the value
        if (self::validateKey($key) && self::validateValue($value)) {

            /*
             * Only one entry per key is allowed. In this case we need to overwrite the vendor entry
             * upon reentry to the tracing system and ensure the updated entry is at the beginning of
             * the list. This means we place it the back for now and it will be at the beginning once
             * we reverse the order back during build().
             */
            if (array_key_exists($key, $clonedTracestate->traceState)) {
                unset($clonedTracestate->traceState[$key]);
            }

            // Add new or updated entry to the back of the list.
            $clonedTracestate->traceState[$key] = $value;
        }

        return $clonedTracestate;
    }

    /**
     * {@inheritdoc}
     */
    public function without(string $key): API\TraceState
    {
        $clonedTracestate = clone $this;

        //TODO: Log if we can't unset the value
        if ($key !== '') {
            unset($clonedTracestate->traceState[$key]);
        }

        return $clonedTracestate;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key): ?string
    {
        return $this->traceState[$key] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getListMemberCount(): int
    {
        return count($this->traceState);
    }

    /**
     * {@inheritdoc}
     */
    public function build(): ?string
    {
        if (!empty($this->traceState)) {
            $clonedTracestate = clone $this;

            // Reverse the order back to the original to ensure new entries are at the beginning.
            $clonedTracestate->traceState = array_reverse($clonedTracestate->traceState);

            array_walk(
                $clonedTracestate->traceState,
                function (&$v, $k) {
                    $v = $k . self::LIST_MEMBER_KEY_VALUE_SPLITTER . $v;
                }
            );

            return implode(self::LIST_MEMBERS_SEPARATOR, $clonedTracestate->traceState);
        }

        return null;
    }

    /**
     * Parse the raw tracestate header into the TraceState object. Since new or updated entries must
     * be added to the beginning of the list, the key-value pairs in the TraceState object will be
     * stored in reverse order. This ensures new entries added to the TraceState object are at the
     * beginning when we reverse the order back again while building the final tracestate header.
     *
     * Ex:
     *      tracestate = 'vendor1=value1,vendor2=value2'
     *
     *                              ||
     *                              \/
     *
     *      $this->tracestate = ['vendor2' => 'value2' ,'vendor1' => 'value1']
     *
     */
    private function parse(string $rawTracestate): array
    {
        $parsedTracestate = [];

        if (\strlen($rawTracestate) <= self::MAX_TRACESTATE_LENGTH) {
            $listMembers = explode(self::LIST_MEMBERS_SEPARATOR, $rawTracestate);

            if (count($listMembers) > self::MAX_TRACESTATE_LIST_MEMBERS) {

                // Truncate the tracestate if it exceeds the maximum list-members allowed
                // TODO: Log a message when truncation occurs
                $listMembers = array_slice($listMembers, 0, self::MAX_TRACESTATE_LIST_MEMBERS);
            }

            foreach ($listMembers as $listMember) {
                $vendor = explode(self::LIST_MEMBER_KEY_VALUE_SPLITTER, $listMember);

                // There should only be one list-member per vendor separated by '='
                if (count($vendor) == 2) {

                    // TODO: Log if we can't validate the key and value
                    if (self::validateKey($vendor[0]) && self::validateValue($vendor[1])) {
                        $parsedTracestate[$vendor[0]] = $vendor[1];
                    }
                }
            }
        }

        /*
         * Reversing the tracestate ensures the new entries added to the TraceState object are at
         * the beginning when we reverse it back during build().
        */
        return array_reverse($parsedTracestate);
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
    private function validateKey(string $key): bool
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
    private function validateValue(string $key): bool
    {
        return (preg_match(self::VALID_VALUE_BASE_REGEX, $key) !== 0)
            && (preg_match(self::INVALID_VALUE_COMMA_EQUAL_REGEX, $key) === 0);
    }
}
