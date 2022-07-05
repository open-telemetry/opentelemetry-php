<?php

declare(strict_types=1);

require __DIR__ . '/../../../vendor/autoload.php';

use Prometheus\Storage\Redis;

Redis::setDefaultOptions(
    [
        'host' => 'redis',
        'port' => 6379,
        'password' => null,
        'timeout' => 0.1, // in seconds
        'read_timeout' => '10', // in seconds
        'persistent_connections' => false,
    ]
);

trigger_error('Prometheus exporter currently not supported', E_USER_WARNING);
