<?php declare(strict_types=1);
namespace API\Trace;

use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\API\Trace\TraceStateInterface;
use PHPUnit\Framework\TestCase;
use function array_reverse;
use function chr;
use function implode;
use function range;
use function str_repeat;

/**
 * @covers \OpenTelemetry\API\Trace\TraceState
 */
final class TraceStateOTelTest extends TestCase {

    /**
     * @dataProvider emptyTraceState
     */
    public function testSetDoesNotModifyOriginalTraceState(TraceStateInterface $traceState): void {
        $traceState->with('key', 'value');

        $this->assertNull($traceState->get('key'));
    }

    /**
     * @dataProvider emptyTraceState
     */
    public function testSetReturnsTraceStateWithSetValue(TraceStateInterface $traceState): void {
        $traceState = $traceState->with('key', 'value');

        $this->assertSame('value', $traceState->get('key'));
    }

    /**
     * @dataProvider emptyTraceState
     */
    public function testSetReturnsTraceStateWithOverriddenValue(TraceStateInterface $traceState): void {
        $traceState = $traceState->with('key', 'value');
        $traceState = $traceState->with('key', 'other');

        $this->assertSame('other', $traceState->get('key'));
    }

    /**
     * @dataProvider emptyTraceState
     */
    public function testSetIsAddedToBeginningOfTraceState(TraceStateInterface $traceState): void {
        $traceState = $traceState->with('key1', 'value1');
        $traceState = $traceState->with('key2', 'value2');

        $this->assertSame('key2=value2,key1=value1', (string) $traceState);
    }

    /**
     * @dataProvider emptyTraceState
     */
    public function testSetIsAddedToBeginningOfTraceStateOnOverriddenValue(TraceStateInterface $traceState): void {
        $traceState = $traceState->with('key1', 'value1');
        $traceState = $traceState->with('key2', 'value2');
        $traceState = $traceState->with('key1', 'value3');

        $this->assertSame('key1=value3,key2=value2', (string) $traceState);
    }

    /**
     * @dataProvider emptyTraceState
     */
    public function testSetIsAddedToBeginningOfTraceStateOnOverriddenSameValue(TraceStateInterface $traceState): void {
        $traceState = $traceState->with('key1', 'value1');
        $traceState = $traceState->with('key2', 'value2');
        $traceState = $traceState->with('key1', 'value1');

        $this->assertSame('key1=value1,key2=value2', (string) $traceState);
    }

    /**
     * @dataProvider emptyTraceState
     */
    public function testDeleteDoesNotModifyOriginalTraceState(TraceStateInterface $traceState): void {
        $traceState = $traceState->with('key', 'value');
        $traceState->without('key');

        $this->assertSame('value', $traceState->get('key'));
    }

    /**
     * @dataProvider emptyTraceState
     */
    public function testDeleteReturnsTraceStateWithUnsetValue(TraceStateInterface $traceState): void {
        $traceState = $traceState->with('key', 'value');
        $traceState = $traceState->without('key');

        $this->assertNull($traceState->get('key'));
    }

    /**
     * @dataProvider emptyTraceState
     */
    public function testAllowsOnly32Members(TraceStateInterface $traceState): void {
        for ($i = 0; $i < 33; $i++) {
            $traceState = $traceState->with('key' . $i, 'test');
        }

        $this->assertSame(32, $traceState->getListMemberCount());
    }

    /**
     * @dataProvider validKeyValue
     */
    public function testValidKeyValue(TraceStateInterface $traceState, string $key, string $value): void {
        $traceState = $traceState->with($key, $value);

        $this->assertSame($value, $traceState->get($key));
    }

    /**
     * @dataProvider invalidKeyValue
     */
    public function testInvalidKeyValue(TraceStateInterface $traceState, string $key, string $value): void {
        $traceState = $traceState->with($key, $value);

        $this->assertNull($traceState->get($key));
    }

    public static function emptyTraceState(): array {
        return [
            [new TraceState()],
        ];
    }

    public static function validKeyValue(): array {
        return [
            [new TraceState(), 'valid@key', 'value'],
            [new TraceState(), 'valid@key', '0'],
            [new TraceState(), 'abcdefghijklmnopqrstuvwxyz0123456789_-*/', 'value'],
            [new TraceState(), 'abcdefghijklmnopqrstuvwxyz0123456789_-*/@a1234_-*/', 'value'],
            [new TraceState(), 'key', strtr(implode(range(chr(0x20), chr(0x7E))), [',' => '', '=' => ''])],
            [new TraceState(), str_repeat('a', 256), 'value'],
            [new TraceState(), 'key', str_repeat('a', 256)],
        ];
    }

    public static function invalidKeyValue(): array {
        return [
            [new TraceState(), '', 'value'],
            [new TraceState(), 'invalid.key', 'value'],
            [new TraceState(), 'invalid-key@', 'value'],
            [new TraceState(), '0invalid-key', 'value'],
            [new TraceState(), str_repeat('a', 257), 'value'],
            [new TraceState(), 'invalid@key_____________', 'value'],
            [new TraceState(), '-invalid-key@id', 'value'],
            [new TraceState(), 'invalid-key@0id', 'value'],
            [new TraceState(), 'invalid-key@@id', 'value'],

            [new TraceState(), 'key', ''],
            [new TraceState(), 'key', 'invalid-value '],
            [new TraceState(), 'key', 'invalid,value'],
            [new TraceState(), 'key', 'invalid=value'],
            [new TraceState(), 'key', str_repeat('a', 257)],
        ];
    }

    /**
     * @dataProvider toStringConfigurations
     */
    public function testToString(TraceStateInterface $traceState, array $entries, ?int $limit, string $expected): void {
        $this->markTestSkipped('Not yet implemented');

        foreach (array_reverse($entries) as $key => $value) {
            $traceState = $traceState->with($key, $value);
        }

        $this->assertSame($expected, $traceState->toString(limit: $limit));
    }

    public static function toStringConfigurations(): array {
        return [
            [new TraceState(), [], null, ''],
            [new TraceState(), ['key' => 'value'], null, 'key=value'],
            [new TraceState(), ['key' => 'value', 'key2' => 'value2'], null, 'key=value,key2=value2'],
            [new TraceState(), ['key' => 'value', 'key2' => 'value2'], 21, 'key=value,key2=value2'],
            [new TraceState(), ['key' => 'value', 'key2' => 'value2'], 14, 'key=value'],
            [new TraceState(), ['key' => 'value', 'key2' => 'value2'], 9, 'key=value'],
            [new TraceState(), ['key' => 'value', 'key2' => 'value2'], 5, ''],
            [new TraceState(), ['key' => 'value', 'a' => 'b'], 5, ''],
            [new TraceState(), [str_repeat('a', 10) => str_repeat('v', 50), 'key' => 'value', 'key2' => 'value2'], 50, ''],
            [new TraceState(), [str_repeat('a', 10) => str_repeat('v', 150), 'key' => 'value', 'key2' => 'value2'], 50, 'key=value,key2=value2'],
            [new TraceState(), [str_repeat('a', 10) => str_repeat('v', 117), 'key' => 'value', 'key2' => 'value2'], 50, ''],
            [new TraceState(), [str_repeat('a', 10) => str_repeat('v', 118), 'key' => 'value', 'key2' => 'value2'], 50, 'key=value,key2=value2'],
            [new TraceState(), [str_repeat('a', 10) => str_repeat('v', 118), 'key' => 'value', 'key2' => 'value2'], 14, 'key=value'],
        ];
    }

    /**
     * @dataProvider parseConfigurations
     */
    public function testParse(string|array $tracestate, ?string $expected): void {
        $traceState = new TraceState($tracestate);
        $this->assertSame($expected ?? '', (string) $traceState);
    }

    public static function parseConfigurations(): array {
        return [
            ['key=value', 'key=value'],
            ['key=value,key2=value2', 'key=value,key2=value2'],
            ['key=value,key2=value2,key=value3', 'key=value,key2=value2'],

            // [['key=value', 'key2=value2'], 'key=value,key2=value2'],
            // [['key=value', 'key=value2'], 'key=value'],

            ['', ''],
            ['  ', ''],
            ['key=value,,key2=value2', 'key=value,key2=value2'],
            ['key=value,   ,key2=value2', 'key=value,key2=value2'],

            ['0', null],
            ['key =value', null],
        ];
    }
}
