--TEST--
Test mutating a LogRecord during SpanProcessor::onEmit
--FILE--
<?php
require_once 'vendor/autoload.php';

use OpenTelemetry\SDK\Trace\TracerProviderBuilder;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\Context\ContextInterface;

$exporter = (new \OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory())->create();

$one = new class implements \OpenTelemetry\SDK\Logs\LogRecordProcessorInterface {
    public function onEmit(\OpenTelemetry\SDK\Logs\ReadWriteLogRecord $record, ?ContextInterface $context = null): void
    {
        $record->setBody('updated');
        $record->setAttribute('new-key', 'new-value');
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }
};
$two = new \OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor($exporter);

$loggerProvider = (new \OpenTelemetry\SDK\Logs\LoggerProviderBuilder())
    ->addLogRecordProcessor(new \OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor([$one, $two]))
    ->build();

$logger = $loggerProvider->getLogger('test');
$logger->emit(new \OpenTelemetry\API\Logs\LogRecord('body'));
?>
--EXPECTF--
{
    "resource": {
        "attributes": {
            "telemetry.sdk.name": "opentelemetry",
            "telemetry.sdk.language": "php",
            "telemetry.sdk.version": "%s",
            "telemetry.distro.name": "%s",
            "telemetry.distro.version": "%s",
            "service.name": "%s"
        },
        "dropped_attributes_count": 0
    },
    "scopes": [
        {
            "name": "test",
            "version": null,
            "attributes": [],
            "dropped_attributes_count": 0,
            "schema_url": null,
            "logs": [
                {
                    "timestamp": null,
                    "observed_timestamp": %d,
                    "severity_number": 0,
                    "severity_text": null,
                    "body": "updated",
                    "trace_id": "%s",
                    "span_id": "%s",
                    "trace_flags": 0,
                    "attributes": {
                        "new-key": "new-value"
                    },
                    "dropped_attributes_count": 0
                }
            ]
        }
    ]
}