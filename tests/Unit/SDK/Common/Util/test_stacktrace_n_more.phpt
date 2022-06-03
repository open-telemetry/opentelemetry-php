--TEST--
Frames should only be collapsed iff matching frames of enclosing exception
--FILE--
<?php
use OpenTelemetry\SDK\Common\Util\TracingUtil;

require_once 'vendor/autoload.php';

function create(?Throwable $e = null): Throwable {
    return new Exception('', 0, $e);
}

$outer = create(create());
echo TracingUtil::formatStackTrace($outer), "\n", "\n";

$inner = create();
$outer = create($inner);
echo TracingUtil::formatStackTrace($outer), "\n", "\n";
?>
--EXPECTF--
Exception
	at create(%s:%d)
	at {main}(%s:%d)
Caused by: Exception
	... 2 more

Exception
	at create(%s:%d)
	at {main}(%s:%d)
Caused by: Exception
	at create(%s:%d)
	at {main}(%s:%d)
