<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

enum State
{
    case ENABLED;
    case DISABLED;
}
