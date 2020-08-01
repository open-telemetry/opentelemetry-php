<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics;

use Webmozart\Assert\Assert;

trait HasLabels
{
    /**
     * @var array $labels
     */
    protected $labels = [];

    /**
     * {@inheritDoc}
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * {@inheritDoc}
     */
    public function setLabels(array $labels)
    {
        foreach ($labels as $label) {
            Assert::string($label, 'The label is expected to be a string. Got: %s');
        }

        $this->labels = $labels;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addLabel(string $label)
    {
        $this->labels[] = $label;

        return $this;
    }
}
