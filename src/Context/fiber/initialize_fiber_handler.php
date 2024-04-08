<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

if (ZendObserverFiber::isEnabled()) {
    ZendObserverFiber::init();
}
