<?php

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

enum Version: string
{
    case VERSION_1_44_0 = '1.44.0';
    case VERSION_1_43_0 = '1.43.0';
    case VERSION_1_42_0 = '1.42.0';
    case VERSION_1_41_1 = '1.41.1';
    case VERSION_1_41_0 = '1.41.0';
    case VERSION_1_40_0 = '1.40.0';
    case VERSION_1_39_0 = '1.39.0';
    case VERSION_1_38_0 = '1.38.0';
    case VERSION_1_37_0 = '1.37.0';
    case VERSION_1_36_0 = '1.36.0';
    case VERSION_1_32_0 = '1.32.0';
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
