<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

echo 'Starting baggage example' . PHP_EOL;

$propagator = \OpenTelemetry\API\Baggage\Propagation\BaggagePropagator::getInstance();
$scopes = [];

//extract baggage from a carrier (eg, inbound http request headers), and store in context
$carrier = [
    'baggage' => 'key1=value1,key2=value2;property1',
];
echo 'Initial baggage input: ' . json_encode($carrier) . PHP_EOL;
echo 'Extracting baggage from carrier...' . PHP_EOL;
$context = $propagator->extract($carrier);
$scopes[] = $context->activate();

//get the baggage, and extract values from it
echo 'Retrieving baggage values...' . PHP_EOL;
$baggage = \OpenTelemetry\API\Baggage\Baggage::getCurrent();
echo 'key1: ' . $baggage->getValue('key1') . PHP_EOL;
echo 'key2: ' . $baggage->getValue('key2') . PHP_EOL;
echo 'key2 metadata: ' . $baggage->getEntry('key2')->getMetadata()->getValue() . PHP_EOL;

//remove a value from baggage and add a value, see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/baggage/api.md#clear-baggage-in-the-context
echo 'removing key1, adding key3 to baggage...' . PHP_EOL;
$scopes[] = $baggage->removeValue('key1')->setValue('key3', 'value3')->activate();

//extract baggage from context, and store in a different carrier (eg, outbound http request headers)
$out = [];
$propagator->inject($out);
echo 'Extracted baggage: ' . json_encode($out) . PHP_EOL;

//clear baggage (to avoid sending to an untrusted process)
echo 'Clearing baggage...' . PHP_EOL;
$scopes[] = \OpenTelemetry\API\Baggage\Baggage::getEmpty()->activate();
$cleared = [];
$propagator->extract($cleared);
echo 'Extracted sanitised baggage: ' . json_encode($cleared) . PHP_EOL;

//detach scopes
foreach (array_reverse($scopes) as $scope) {
    $scope->detach();
}

echo 'Finished baggage example' . PHP_EOL;
