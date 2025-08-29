final <?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

use Symfony\Component\Config\Resource\ResourceInterface;

class EnvResource implements ResourceInterface
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $value,
    ) {
    }

    #[\Override]
    public function __toString(): string
    {
        return 'env.' . $this->name;
    }
}
