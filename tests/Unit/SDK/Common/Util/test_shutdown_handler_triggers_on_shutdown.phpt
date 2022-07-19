--TEST--
ShutdownHandler is triggered on shutdown
--FILE--
<?php
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;

class ShutdownTest {
    public function shutdown(): void {
        var_dump(spl_object_id($this));
    }
}

$a = new ShutdownTest();
$b = new ShutdownTest();

require_once 'vendor/autoload.php';

ShutdownHandler::register([$a, 'shutdown']);
ShutdownHandler::register([$b, 'shutdown']);

?>
--EXPECT--
int(1)
int(2)
