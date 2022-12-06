<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

echo 'Starting baggage example' . PHP_EOL;

$propagator = \OpenTelemetry\API\Baggage\Propagation\BaggagePropagator::getInstance();

//extract baggage from a carrier (eg, inbound http request headers), and store in context
$carrier = [
    'baggage' => 'key1=value1,key2=value2;property1',
];
echo 'Extracting baggage from carrier...' . PHP_EOL;
$context = $propagator->extract($carrier);
$scope = $context->activate();

//get the baggage, and extract values from it
echo 'Retrieving baggage values...' . PHP_EOL;
$baggage = \OpenTelemetry\API\Baggage\Baggage::getCurrent();
echo 'key1: ' . $baggage->getValue('key1') . PHP_EOL;
echo 'key2: ' . $baggage->getValue('key2') . PHP_EOL;
echo 'key2 metadata: ' . $baggage->getEntry('key2')->getMetadata()->getValue() . PHP_EOL;

//extract baggage from context, and store in a different carrier (eg, outbound http request headers)
$out = [];
$propagator->inject($out);
echo 'Extracted baggage: ' . json_encode($out) . PHP_EOL;
$scope->detach();

echo 'Finished baggage example' . PHP_EOL;