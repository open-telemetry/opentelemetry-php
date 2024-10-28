<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;

ServiceLoader::register(TransportFactoryInterface::class, GrpcTransportFactory::class);
