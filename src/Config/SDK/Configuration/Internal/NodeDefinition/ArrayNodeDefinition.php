<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

use OpenTelemetry\Config\SDK\Configuration\IgnoresUnknownProviders;
use OpenTelemetry\Config\SDK\Configuration\Internal\Node\ArrayNode;
use OpenTelemetry\Config\SDK\Configuration\Internal\Node\PrototypedArrayNode;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * @internal
 * @template TParent of NodeParentInterface|null = null
 * @extends \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition<TParent>
 */
class ArrayNodeDefinition extends \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition implements IgnoresUnknownProviders
{
    use NodeDefinitionTrait;

    private bool $defaultValueSet = false;
    private bool $ignoreUnknownProviders = false;

    public function __construct(?string $name, ?NodeParentInterface $parent = null)
    {
        parent::__construct($name, $parent);

        $this->nullEquivalent = null;
    }

    #[\Override]
    public function attribute(string $key, mixed $value): static
    {
        if ($key === IgnoresUnknownProviders::ATTRIBUTE) {
            $this->ignoreUnknownProviders = (bool) $value;
        }

        return parent::attribute($key, $value);
    }

    #[\Override]
    public function ignoresUnknownProviders(): bool
    {
        return $this->ignoreUnknownProviders;
    }

    #[\Override]
    protected function createNode(): NodeInterface
    {
        $node = parent::createNode();

        /** @phpstan-ignore-next-line */
        $node = match (true) {
            $node instanceof \Symfony\Component\Config\Definition\PrototypedArrayNode => PrototypedArrayNode::fromNode($node),
            $node instanceof \Symfony\Component\Config\Definition\ArrayNode => ArrayNode::fromNode($node),
        };

        $node->setAllowEmptyValue($this->allowEmptyValue);
        if ($this->defaultValueSet) {
            $node->setDefaultValue($this->defaultValue);
        }

        return $node;
    }

    #[\Override]
    public function defaultValue(mixed $value): static
    {
        $this->defaultValueSet = true;
        $this->defaultValue = $value;

        return $this;
    }
}
