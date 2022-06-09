<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace;

use OpenTelemetry\API\Trace\TraceState;
use PHPUnit\Framework\TestCase;
use function str_repeat;
use function strlen;

/**
 * @covers OpenTelemetry\API\Trace\TraceState
 */
class TraceStateTest extends TestCase
{
    public function test_get_tracestate_value(): void
    {
        $tracestate = new TraceState('vendor1=value1');

        $this->assertSame('value1', $tracestate->get('vendor1'));
    }

    public function test_with_tracestate_value(): void
    {
        $tracestate = new TraceState('vendor1=value1');
        $tracestateWithNewValue = $tracestate->with('vendor2', 'value2');

        // New entry is included in the new TraceState object
        $this->assertSame('value2', $tracestateWithNewValue->get('vendor2'));
        $this->assertNull($tracestate->get('vendor2'));

        // New entry is placed at the beginning of the tracestate header
        $this->assertSame('vendor2=value2,vendor1=value1', (string) $tracestateWithNewValue);

        $tracestateWithUpdatedValue = $tracestateWithNewValue->with('vendor1', 'newValue1');

        // The updated entry is overwritten and placed at the beginning of the header
        $this->assertSame('value1', $tracestateWithNewValue->get('vendor1'));
        $this->assertSame('newValue1', $tracestateWithUpdatedValue->get('vendor1'));
        $this->assertSame('vendor1=newValue1,vendor2=value2', (string) $tracestateWithUpdatedValue);

        // A new entry containing an invalid key will not be added
        $tracestateWithInvalidKey = $tracestate->with('@', 'value');
        $this->assertNull($tracestateWithInvalidKey->get('@'));
        $this->assertSame((string) $tracestate, (string) $tracestateWithInvalidKey);

        // A new entry containing an invalid value will not be added
        $tracestateWithInvalidValue = $tracestate->with('vendor2', 'value' . chr(0x19) . '1');
        $this->assertNull($tracestateWithInvalidValue->get('vendor2'));
        $this->assertSame((string) $tracestate, (string) $tracestateWithInvalidValue);
    }

    public function test_without_tracestate_value(): void
    {
        $tracestate = new TraceState('vendor1=value1,vendor2=value2');
        $tracestateWithoutNewValue = $tracestate->without('vendor1');

        $this->assertNull($tracestateWithoutNewValue->get('vendor1'));
        $this->assertSame('value2', $tracestateWithoutNewValue->get('vendor2'));
        $this->assertSame('value1', $tracestate->get('vendor1'));
        $this->assertSame('value2', $tracestate->get('vendor2'));
    }

    public function test_to_string_tracestate(): void
    {
        $tracestate = new TraceState('vendor1=value1');
        $emptyTracestate = new TraceState();

        $this->assertSame('vendor1=value1', (string) $tracestate);
        $this->assertSame(0, $emptyTracestate->getListMemberCount());
        $this->assertEmpty((string) $emptyTracestate);
    }

    public function test_max_tracestate_list_members(): void
    {
        // Build a tracestate with the max 32 values. Ex '0=0,1=1,...,31=31'
        $rawTraceState = range(0, TraceState::MAX_TRACESTATE_LIST_MEMBERS - 1);
        array_walk($rawTraceState, static function (&$v, $k) {
            $v = 'k' . $k . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . 'v' . $v;
        });

        /**
         * @var array $rawTraceState
         * @see https://github.com/vimeo/psalm/issues/6394
         */
        $this->assertCount(TraceState::MAX_TRACESTATE_LIST_MEMBERS, $rawTraceState);

        $validTracestate = new TraceState(implode(TraceState::LIST_MEMBERS_SEPARATOR, $rawTraceState));
        $this->assertSame(TraceState::MAX_TRACESTATE_LIST_MEMBERS, $validTracestate->getListMemberCount());

        // Add a list-member to the tracestate that exceeds the max of 32. This will cause the tracestate to be discarded.
        $rawTraceState[32] = 'k32' . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . 'v32';
        $this->assertCount(TraceState::MAX_TRACESTATE_LIST_MEMBERS + 1, $rawTraceState);

        $truncatedTracestate = new TraceState(implode(TraceState::LIST_MEMBERS_SEPARATOR, $rawTraceState));
        $this->assertSame(0, $truncatedTracestate->getListMemberCount());
    }

    public function test_max_tracestate_length(): void
    {
        // Build a vendor key with a length of 256 characters. The max characters allowed.
        $vendorKey = str_repeat('k', TraceState::MAX_TRACESTATE_LENGTH / 2);

        // Build a vendor value with a length of 255 characters. One below the max allowed.
        $vendorValue = str_repeat('v', TraceState::MAX_TRACESTATE_LENGTH / 2 - 1);

        // tracestate length = 513 characters (not accepted).
        $rawTraceState = $vendorKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $vendorValue . 'v';
        $this->assertGreaterThan(TraceState::MAX_TRACESTATE_LENGTH, strlen($rawTraceState));

        $validTracestate = new TraceState($rawTraceState);
        $this->assertNull($validTracestate->get($vendorKey));

        // tracestate length = 512 characters (accepted).
        $rawTraceState = $vendorKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $vendorValue;
        $this->assertSame(TraceState::MAX_TRACESTATE_LENGTH, strlen($rawTraceState));

        $validTracestate = new TraceState($rawTraceState);
        $this->assertSame($rawTraceState, (string) $validTracestate);
    }

    public function test_validate_key(): void
    {
        // Valid keys
        $validKeys = 'a-b=1,c*d=2,e/f=3,g_h=4,01@i-j=5';
        $tracestate = new TraceState($validKeys);

        $this->assertSame('1', $tracestate->get('a-b'));
        $this->assertSame('2', $tracestate->get('c*d'));
        $this->assertSame('3', $tracestate->get('e/f'));
        $this->assertSame('4', $tracestate->get('g_h'));
        $this->assertSame('5', $tracestate->get('01@i-j'));
        $this->assertSame($validKeys, (string) $tracestate);

        // Mixed invalid keys in with valid ones
        $mixedInvalidKeys = 'a=1=,c*d=2,@e/f=3,g_h=4,I-j=5,k&l=6';
        $tracestate = new TraceState($mixedInvalidKeys);

        // Drop all keys on an invalid key
        $this->assertSame(0, $tracestate->getListMemberCount());

        // Tests a valid key with a length of 256 characters. The max characters allowed.
        $validKey = str_repeat('k', 256);
        $validValue = 'v';
        $tracestate = new TraceState($validKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $validValue);

        $this->assertSame(256, strlen($validKey));
        $this->assertSame($validValue, $tracestate->get($validKey));
        $this->assertSame($validKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $validValue, (string) $tracestate);

        // Tests an invalid key with a length of 257 characters. One more than the max characters allowed.
        $invalidKey = str_repeat('k', 257);
        $tracestate = new TraceState($invalidKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $validValue);

        $this->assertSame(257, strlen($invalidKey));
        $this->assertNull($tracestate->get($invalidKey));
    }

    public function test_validate_value(): void
    {
        // Tests values are within the range of 0x20 to 0x7E characters
        $tracestate =    'char1=value' . chr(0x19) . '1'
                      . ',char2=value' . chr(0x20) . '2'
                      . ',char3=value' . chr(0x7E) . '3'
                      . ',char4=value' . chr(0x7F) . '4';

        $parsedTracestate = new TraceState($tracestate);

        // Entire tracestate is dropped since the value for char1 is invalid.
        $this->assertSame(0, $parsedTracestate->getListMemberCount());

        // Tests a valid value with a length of 256 characters. The max allowed.
        $validValue = str_repeat('v', 256);
        $validKey = 'k';
        $tracestate = new TraceState($validKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $validValue);

        $this->assertSame(256, strlen($validValue));
        $this->assertSame($validValue, $tracestate->get($validKey));
        $this->assertSame($validKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $validValue, (string) $tracestate);

        // Tests an invalid value with a length of 257 characters. One more than the max allowed.
        $invalidValue = str_repeat('v', 257);
        $tracestate = new TraceState($validKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $invalidValue);

        $this->assertSame(257, strlen($invalidValue));
        $this->assertNull($tracestate->get($validKey));
    }
}
