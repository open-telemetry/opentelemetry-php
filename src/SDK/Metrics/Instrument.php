<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final class Instrument
{
    /**
     * @var string|InstrumentType
     * @readonly
     */
    public $type;
    /**
     * @readonly
     */
    public string $name;
    /**
     * @readonly
     */
    public ?string $unit;
    /**
     * @readonly
     */
    public ?string $description;
    /**
     * @param string|InstrumentType $type
     */
    public function __construct($type, string $name, ?string $unit, ?string $description)
    {
        $this->type = $type;
        $this->name = $name;
        $this->unit = $unit;
        $this->description = $description;
    }
}
