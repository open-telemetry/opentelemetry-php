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
        string $protocol,
        string $host,
        ?string $path = null,
        ?int $port = null,
        ?array $options = null,
        ?string $user = null,
        ?string $password = null
    ) {
        $this->type = $type;
        $this->protocol = $protocol;
        $this->host = $host;
        $this->path = $path;
        $this->port = $port;
        $this->options = $options ?? [];
        $this->user = $user;
        $this->password = $password;
    }

    public static function create(
        string $type,
        string $protocol,
        string $host,
        ?string $path = null,
        ?int $port = null,
        ?array $options = null,
        ?string $user = null,
        ?string $password = null
    ): self {
        return new self($type, $protocol, $host, $path, $port, $options, $user, $password);
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
     * Returns the endpoint (DSN without type and options)
     */
    public function getEndpoint(): string
    {
        return $this->endpoint ??= $this->assembleEndpoint();
    }

    public function asConfigArray(): array
    {
        return [
            'type' => $this->getType(),
            'url' => $this->getEndpoint(),
            'options' => $this->getOptions(),
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getScheme(): string
    {
        return $this->scheme ??= $this->assembleScheme();
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

    private function assembleScheme(): string
    {
        return sprintf('%s+%s', $this->getType(), $this->getProtocol());
    }

    private function assembleEndpoint(): string
    {
        $dsn = sprintf(
            '%s://',
            $this->getProtocol()
        );

        if ($this->getUser() !== null) {
            $dsn .= $this->getPassword() !== null
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

        $dsn .= $this->getHost();
        $dsn .= $this->getPort() !== null ? sprintf(
            ':%s',
            (string) $this->getPort(),
        ) : '';

        return $dsn . ($this->getPath() ?? '');
    }
}
