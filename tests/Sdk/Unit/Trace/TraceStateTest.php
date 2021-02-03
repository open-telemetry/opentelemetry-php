<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\TraceState;
use PHPUnit\Framework\TestCase;

class TraceStateTest extends TestCase
{
    /**
     * @test
     */
    public function testGetTracestateValue()
    {
        $tracestate = new TraceState('vendor1=value1');

        $this->assertSame('value1', $tracestate->get('vendor1'));
    }

    /**
     * @test
     */
    public function testWithTracestateValue()
    {
        $tracestate = new TraceState('vendor1=value1');
        $tracestateWithNewValue = $tracestate->with('vendor2', 'value2');

        $this->assertSame('value2', $tracestateWithNewValue->get('vendor2'));
        $this->assertNull($tracestate->get('vendor2'));
    }

    /**
     * @test
     */
    public function testWithoutTracestateValue()
    {
        $tracestate = new TraceState('vendor1=value1,vendor2=value2');
        $tracestateWithoutNewValue = $tracestate->without('vendor1');

        $this->assertNull($tracestateWithoutNewValue->get('vendor1'));
        $this->assertSame('value2', $tracestateWithoutNewValue->get('vendor2'));
        $this->assertSame('value1', $tracestate->get('vendor1'));
        $this->assertSame('value2', $tracestate->get('vendor2'));
    }

    /**
     * @test
     */
    public function testBuildTracestate()
    {
        $tracestate = new TraceState('vendor1=value1');
        $emptyTracestate = new TraceState();

        $this->assertSame('vendor1=value1', $tracestate->build());
        $this->assertSame(0, $emptyTracestate->getListMemberCount());
        $this->assertNull($emptyTracestate->build());
    }

    /**
     * @test
     */
    public function testMaxTracestateListMembers()
    {
        // Build a tracestate with the max 32 values. Ex '0=0,1=1,...,31=31'
        $rawTraceState = range(0, TraceState::MAX_TRACESTATE_LIST_MEMBERS - 1);
        array_walk($rawTraceState, function (&$v, $k) {
            $v = 'k' . $k . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . 'v' . $v;
        });
        $this->assertSame(TraceState::MAX_TRACESTATE_LIST_MEMBERS, count($rawTraceState));

        $validTracestate = new TraceState(implode(Tracestate::LIST_MEMBERS_SEPARATOR, $rawTraceState));
        $this->assertSame(TraceState::MAX_TRACESTATE_LIST_MEMBERS, $validTracestate->getListMemberCount());

        // Add a list-member to the tracestate that exceeds the max of 32. This will cause it to be truncated
        $rawTraceState['32'] = 'k32' . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . 'v32';
        $this->assertSame(TraceState::MAX_TRACESTATE_LIST_MEMBERS + 1, count($rawTraceState));

        $truncatedTracestate = new TraceState(implode(Tracestate::LIST_MEMBERS_SEPARATOR, $rawTraceState));
        $this->assertSame(TraceState::MAX_TRACESTATE_LIST_MEMBERS, $truncatedTracestate->getListMemberCount());
    }

    /**
     * @test
     */
    public function testMaxTracestateLength()
    {
        // Build a vendor key with a length of 256 characters. The max characters allowed.
        $vendorKey = \str_repeat('v', TraceState::MAX_TRACESTATE_LENGTH / 2);

        // Build a vendor value with a length of 255 characters. One below the max allowed.
        $vendorValue = \str_repeat('a', TraceState::MAX_TRACESTATE_LENGTH / 2 - 1);

        // tracestate length = 513 characters (not accepted).
        $rawTraceState = $vendorKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $vendorValue . 'a';
        $this->assertGreaterThan(TraceState::MAX_TRACESTATE_LENGTH, \strlen($rawTraceState));

        $validTracestate = new TraceState($rawTraceState);
        $this->assertNull($validTracestate->get($vendorKey));

        // tracestate length = 512 characters (accepted).
        $rawTraceState = $vendorKey . TraceState::LIST_MEMBER_KEY_VALUE_SPLITTER . $vendorValue;
        $this->assertSame(TraceState::MAX_TRACESTATE_LENGTH, \strlen($rawTraceState));

        $validTracestate = new TraceState($rawTraceState);
        $this->assertSame($rawTraceState, $validTracestate->build());
    }

    /**
     * @test
     */
    public function testValidateKey()
    {
        // Valid keys
        $validKeys = 'a-b=1,c*d=2,e/f=3,g_h=4,01@i-j=5';
        $tracestate = new TraceState($validKeys);

        $this->assertSame('1', $tracestate->get('a-b'));
        $this->assertSame('2', $tracestate->get('c*d'));
        $this->assertSame('3', $tracestate->get('e/f'));
        $this->assertSame('4', $tracestate->get('g_h'));
        $this->assertSame('5', $tracestate->get('01@i-j'));
        $this->assertSame($validKeys, $tracestate->build());

        // Mixed invalid keys in with valid ones
        $mixedInvalidKeys = 'a=1=,c*d=2,@e/f=3,g_h=4,I-j=5,k&l=6';
        $tracestate = new TraceState($mixedInvalidKeys);

        $this->assertNull($tracestate->get('a=1'));
        $this->assertNull($tracestate->get('@e'));
        $this->assertNull($tracestate->get('I-j'));
        $this->assertNull($tracestate->get('k&l'));
        $this->assertSame('2', $tracestate->get('c*d'));
        $this->assertSame('4', $tracestate->get('g_h'));
        $this->assertSame('c*d=2,g_h=4', $tracestate->build());
    }

    /**
     * @test
     */
    public function testvalidateValue()
    {
        // Tests values are within the range of 0x20 to 0x7E characters
        $tracestate =   'char1=value' . chr(0x19) . '1'
                      . ',char2=value' . chr(0x20) . '2'
                      . ',char3=value' . chr(0x7E) . '3'
                      . ',char4=value' . chr(0x7F) . '4';

        $parsedTracestate = new TraceState($tracestate);

        $this->assertNull($parsedTracestate->get('char1'));
        $this->assertNull($parsedTracestate->get('char4'));
        $this->assertSame('value' . chr(0x20) . '2', $parsedTracestate->get('char2'));
        $this->assertSame('value' . chr(0x7E) . '3', $parsedTracestate->get('char3'));
        $this->assertSame('char2=value' . chr(0x20) . '2,char3=value' . chr(0x7E) . '3', $parsedTracestate->build());
    }
}
