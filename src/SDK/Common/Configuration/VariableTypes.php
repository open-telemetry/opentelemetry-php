<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

interface VariableTypes
{
    /**
     * A single boolean value represented as a string or integer ('true', 'false', 0, 1)
     * example: value1
     */
    public const BOOL = 'bool';

    /**
     * A single string value
     * example: value1
     */
    public const STRING = 'string';

    /**
     * A single integer value
     * example: 5000
     */
    public const INTEGER = 'integer';

    /**
     * A single float value
     * example: 10.5
     */
    public const FLOAT = 'float';

    /**
     * A single float value between 0.0 and 1.0
     * example: 0.5
     */
    public const RATIO = 'ratio';

    /**
     * A single string value from a fixed list of values
     * example values: value1, value2, value3
     * example: value1
     */
    public const ENUM = 'enum';

    /**
     * A comma separated list of single string values
     * example: value1,value2,value3
     */
    public const LIST = 'list';

    /**
     * A comma separated list of key-value pairs
     * example: key1=value1,key2=value2
     */
    public const MAP = 'map';

    /**
     * Values of mixed type
     */
    public const MIXED = 'mixed';
}
