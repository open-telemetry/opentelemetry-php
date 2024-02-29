<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

final class Instrument
{
    /**
     * @param string|InstrumentType $type
     */
    public function __construct(
        /**
         * @readonly
         */
        public $type,
        /**
         * @readonly
         */
        public string $name,
        /**
         * @readonly
         */
        public ?string $unit,
        /**
         * @readonly
         */
        public ?string $description,
        /**
         * @readonly
         */
        public array $advisory = []
    ) {
    }
}
