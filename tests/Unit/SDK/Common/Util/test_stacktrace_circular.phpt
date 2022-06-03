--TEST--
Circular reference in exception
--FILE--
<?php
use OpenTelemetry\SDK\Common\Util\TracingUtil;

require_once 'vendor/autoload.php';

class TestException extends Exception {
    public function __construct(string $message = "", int $code = 0) {
        parent::__construct($message, $code, new Exception('', 0, $this));
    }
}

echo TracingUtil::formatStackTrace(new TestException()), "\n", "\n";
echo TracingUtil::formatStackTrace(new TestException('message')), "\n", "\n";
?>
--EXPECTF--
TestException
	at {main}(%s:%d)
Caused by: Exception
	at TestException.__construct(%s:%d)
	... 1 more
Caused by: [CIRCULAR REFERENCE: TestException]

TestException: message
	at {main}(%s:%d)
Caused by: Exception
	at TestException.__construct(%s:%d)
	... 1 more
Caused by: [CIRCULAR REFERENCE: TestException: message]
