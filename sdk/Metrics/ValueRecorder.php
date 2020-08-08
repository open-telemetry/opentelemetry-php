<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

use OpenTelemetry\Metrics as API;

class ValueRecorder extends AbstractMetric implements API\ValueRecorder, API\LabelableMetric
{
    use HasLabels;

    /**
     * @var int $value
     */
    protected $value = 0;

    /**
     * Get $type
     *
     * @return  int
     */
    public function getType(): int
    {
        return API\MetricKind::VALUE_RECORDER;
    }

    /**
     * Returns the current value
     *
     * @access	public
     * @return	int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function record($value) : void
    {
        $this->value = $value;
    }
}
