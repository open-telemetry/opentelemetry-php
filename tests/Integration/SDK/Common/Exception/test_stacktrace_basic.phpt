--TEST--
Basic stacktrace format
--FILE--
<?php
use OpenTelemetry\SDK\Common\Exception\StackTraceFormatter;

require_once 'vendor/autoload.php';

class Test {
    public static function createStatic(): Throwable {
        return new Exception();
    }
    public function create(): Throwable {
        return new Exception();
    }
}

function create(): Throwable {
    return new Exception();
}

echo StackTraceFormatter::format(new Exception()), "\n", "\n";
echo StackTraceFormatter::format(new Exception('message')), "\n", "\n";
echo StackTraceFormatter::format(new Exception('outer', 0, new Exception('inner'))), "\n", "\n";

echo StackTraceFormatter::format(create()), "\n", "\n";
echo StackTraceFormatter::format((new Test)->create()), "\n", "\n";
echo StackTraceFormatter::format(Test::createStatic()), "\n", "\n";
?>
--EXPECTF--
Exception
	at {main}(%s:19)

Exception: message
	at {main}(%s:20)

Exception: outer
	at {main}(%s:21)
Caused by: Exception: inner
	... 1 more

Exception
	at create(%s:16)
	at {main}(%s:23)

Exception
	at Test.create(%s:11)
	at {main}(%s:24)

Exception
	at Test.createStatic(%s:8)
	at {main}(%s:25)
