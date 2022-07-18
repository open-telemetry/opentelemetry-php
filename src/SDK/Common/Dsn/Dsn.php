<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dsn;

class Dsn implements DsnInterface
{
    private string $type;
    private string $protocol;
    private string $host;
    private ?string $path;
    private ?int $port;
    private array $options;
    private ?string $user;
    private ?string $password;
    private string $endpoint;
    private string $scheme;

    public function __construct(
        string $type,
        string $scheme,
        string $host,
        ?string $path = null,
        ?int $port = null,
        ?array $options = null,
        ?string $user = null,
        ?string $password = null
    ) {
        $this->type = $type;
        $this->scheme = $scheme;
        $this->host = $host;
        $this->path = $path;
        $this->port = $port;
        $this->options = $options ?? [];
        $this->user = $user;
        $this->password = $password;
    }

    public static function create(
        string $type,
        string $scheme,
        string $host,
        ?string $path = null,
        ?int $port = null,
        ?array $options = null,
        ?string $user = null,
        ?string $password = null
    ): self {
        return new self($type, $scheme, $host, $path, $port, $options, $user, $password);
    }

    public function __toString(): string
    {
        $dsn = sprintf(
            '%s+%s',
            $this->getType(),
            $this->getEndpoint()
        );

        return $dsn . (empty($this->getOptions()) ? '' : '?' . http_build_query($this->getOptions()));
    }

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->endpoint ??= $this->renderEndpoint();
    }

    public function asConfigArray(): array
    {
        return [
            DsnInterface::TYPE_ATTRIBUTE => $this->getType(),
            DsnInterface::URL_ATTRIBUTE => $this->getEndpoint(),
            DsnInterface::OPTIONS_ATTRIBUTE => $this->getOptions(),
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getProtocol(): string
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return $this->protocol ??= $this->renderProtocol();
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    private function renderProtocol(): string
    {
        return sprintf('%s+%s', $this->getType(), $this->getScheme());
    }

    private function renderEndpoint(): string
    {
        $dsn = $this->renderScheme();

        $dsn .= $this->renderUserInfo();
        $dsn .= $this->getHost();
        $dsn .= $this->renderPort();

        return $dsn . ($this->getPath() ?? '');
    }

    private function renderScheme(): string
    {
        return sprintf(
            '%s://',
            $this->getScheme()
        );
    }

    /**
     * @phan-suppress PhanTypeMismatchArgumentNullableInternal
     * @psalm-suppress PossiblyNullArgument
     */
    private function renderUserInfo(): string
    {
        if ($this->getUser() === null) {
            return '';
        }

        return $this->getPassword() !== null
            ? sprintf(
                '%s:%s@',
                $this->getUser(),
                $this->getPassword()
            )
            : sprintf(
                '%s@',
                $this->getUser()
            );
    }

    /**
     * @phan-suppress PhanTypeMismatchArgumentNullableInternal
     * @psalm-suppress PossiblyNullArgument
     */
    private function renderPort(): string
    {
        return $this->getPort() !== null ? sprintf(
            ':%s',
            $this->getPort(),
        ) : '';
    }
}
