<?php

declare(strict_types=1);

\Prometheus\Storage\Redis::setDefaultOptions(
    [
        'host' => 'redis',
        'port' => 6379,
        'password' => null,
        'timeout' => 0.1, // in seconds
        'read_timeout' => '10', // in seconds
        'persistent_connections' => false,
    ]
);

$registry = \Prometheus\CollectorRegistry::getDefault();

$renderer = new \Prometheus\RenderTextFormat();
$result = $renderer->render($registry->getMetricFamilySamples());

header('Content-type: ' . \Prometheus\RenderTextFormat::MIME_TYPE);
echo $result;
