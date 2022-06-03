--TEST--
Basic stacktrace format
--FILE--
<?php
use OpenTelemetry\SDK\Common\Util\TracingUtil;

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

echo TracingUtil::formatStackTrace(new Exception()), "\n", "\n";
echo TracingUtil::formatStackTrace(new Exception('message')), "\n", "\n";
echo TracingUtil::formatStackTrace(new Exception('outer', 0, new Exception('inner'))), "\n", "\n";

echo TracingUtil::formatStackTrace(create()), "\n", "\n";
echo TracingUtil::formatStackTrace((new Test)->create()), "\n", "\n";
echo TracingUtil::formatStackTrace(Test::createStatic()), "\n", "\n";
?>
--EXPECTF--
Exception
	at {main}(test_stacktrace_basic.php:19)

Exception: message
	at {main}(test_stacktrace_basic.php:20)

Exception: outer
	at {main}(test_stacktrace_basic.php:21)
Caused by: Exception: inner
	... 1 more

Exception
	at create(test_stacktrace_basic.php:16)
	at {main}(test_stacktrace_basic.php:23)

Exception
	at Test.create(test_stacktrace_basic.php:11)
	at {main}(test_stacktrace_basic.php:24)

Exception
	at Test.createStatic(test_stacktrace_basic.php:8)
	at {main}(test_stacktrace_basic.php:25)
