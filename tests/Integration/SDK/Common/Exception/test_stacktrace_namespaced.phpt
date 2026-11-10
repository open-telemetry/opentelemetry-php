--TEST--
Namespaced stacktrace format
--FILE--
<?php
namespace Abc\Def;

use Exception;
use OpenTelemetry\SDK\Common\Exception\StackTraceFormatter;
use Throwable;

require_once 'vendor/autoload.php';

class TestException extends Exception {
}

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

echo StackTraceFormatter::format(new TestException()), "\n", "\n";
echo StackTraceFormatter::format(new Exception('outer', 0, new TestException('inner'))), "\n", "\n";

echo StackTraceFormatter::format(create()), "\n", "\n";
echo StackTraceFormatter::format((new Test)->create()), "\n", "\n";
echo StackTraceFormatter::format(Test::createStatic()), "\n", "\n";
?>
--EXPECTF--
Abc.Def.TestException
	at {main}(%s:%d)

Exception: outer
	at {main}(%s:%d)
Caused by: Abc.Def.TestException: inner
	... 1 more

Exception
	at Abc.Def.create(%s:%d)
	at {main}(%s:%d)

Exception
	at Abc.Def.Test.create(%s:%d)
	at {main}(%s:%d)

Exception
	at Abc.Def.Test.createStatic(%s:%d)
	at {main}(%s:%d)
