<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;

/**
 * @internal
 */
final class TreatNullAsUnsetNormalization
{
    const ALLOW_EMPTY = 'allow-empty';

    public function apply(ArrayNodeDefinition $root): void
    {
        foreach ($root->getChildNodeDefinitions() as $childNode) {
            $this->doApply($childNode);
        }
    }

    /**
     * @psalm-suppress PossiblyNullFunctionCall,InaccessibleProperty
     */
    private function allowEmpty(ArrayNodeDefinition $node): bool
    {
        static $accessor; //@todo inaccessible property $node->attributes
        /** @phpstan-ignore-next-line */
        $accessor ??= (static fn (ArrayNodeDefinition $node): ?bool => $node->attributes[TreatNullAsUnsetNormalization::ALLOW_EMPTY] ?? false)
            ->bindTo(null, ArrayNodeDefinition::class);

        return $accessor($node);
    }

    private function doApply(NodeDefinition $node): void
    {
        if (!($node instanceof ArrayNodeDefinition && $this->allowEmpty($node))) {
            $node->beforeNormalization()->ifNull()->thenUnset()->end();
        }

        if ($node instanceof ParentNodeDefinitionInterface) {
            foreach ($node->getChildNodeDefinitions() as $childNode) {
                $this->doApply($childNode);
            }
        }
    }
}
