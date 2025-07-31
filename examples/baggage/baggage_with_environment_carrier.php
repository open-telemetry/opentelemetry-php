<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../vendor/autoload.php';

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\Context\Propagation\EnvironmentGetterSetter;

$propagator = BaggagePropagator::getInstance();
$envGetterSetter = EnvironmentGetterSetter::getInstance();

$isChild = isset($argv[1]) && $argv[1] === 'child';

if (!$isChild) {
    $baggage = Baggage::getBuilder()
        ->set('key1', 'value1')
        ->set('key2', 'value2')
        ->build();
    $baggageScope = $baggage->activate();

    // Inject baggage into environment variables
    $carrier = [];
    $propagator->inject($carrier, $envGetterSetter);

    // Execute child process
    $command = sprintf('%s %s %s', PHP_BINARY, escapeshellarg(__FILE__), 'child');
    $handle = popen($command, 'w');
    if ($handle === false) {
        echo 'Failed to execute child process.' . PHP_EOL;
    } else {
        pclose($handle);
    }

    $baggageScope->detach();
} else {
    // Extract baggage from environment variables
    $context = $propagator->extract([], $envGetterSetter);
    $scope = $context->activate();

    // Get values from baggage
    $baggage = Baggage::getCurrent();
    echo 'key1: ' . $baggage->getValue('key1') . PHP_EOL;
    echo 'key2: ' . $baggage->getValue('key2') . PHP_EOL;

    $scope->detach();
}
