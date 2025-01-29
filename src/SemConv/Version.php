<?php

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

enum Version: string
{
    case VERSION_1_30_0 = '1.30.0';
    case VERSION_1_28_0 = '1.28.0';
    case VERSION_1_27_0 = '1.27.0';
    case VERSION_1_26_0 = '1.26.0';
    case VERSION_1_25_0 = '1.25.0';
    case VERSION_1_24_0 = '1.24.0';
    case VERSION_1_23_1 = '1.23.1';
    case VERSION_1_23_0 = '1.23.0';
    case VERSION_1_22_0 = '1.22.0';
    case VERSION_1_21_0 = '1.21.0';

    public function url(): string
    {
        return 'https://opentelemetry.io/schemas/' . $this->value;
    }
}
