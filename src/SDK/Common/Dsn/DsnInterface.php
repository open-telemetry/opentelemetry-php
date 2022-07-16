<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dsn;

interface DsnInterface
{
    /** @var string  */
    public const TYPE_ATTRIBUTE = 'type';
    /** @var string  */
    public const PROTOCOL_ATTRIBUTE = 'protocol';
    /** @var string  */
    public const SCHEME_ATTRIBUTE = 'scheme';
    /** @var string  */
    public const HOST_ATTRIBUTE = 'host';
    /** @var string  */
    public const PATH_ATTRIBUTE = 'path';
    /** @var string  */
    public const PORT_ATTRIBUTE = 'port';
    /** @var string  */
    public const OPTIONS_ATTRIBUTE = 'options';
    /** @var string  */
    public const USER_ATTRIBUTE = 'user';
    /** @var string  */
    public const PASSWORD_ATTRIBUTE = 'password';
    /** @var string  */
    public const URL_ATTRIBUTE = 'url';

    /** @var string[]  */
    public const CONFIG_ATTRIBUTES = [
        DsnInterface::TYPE_ATTRIBUTE,
        DsnInterface::URL_ATTRIBUTE,
        DsnInterface::OPTIONS_ATTRIBUTE,
    ];

    /**
     * Returns the endpoint (DSN without type and options)
     */
    public function getEndpoint(): string;

    public function asConfigArray(): array;

    public function getType(): string;

    public function getProtocol(): string;

    public function getScheme(): string;

    public function getHost(): string;

    public function getPath(): ?string;

    public function getPort(): ?int;

    public function getOptions(): array;

    public function getOption(string $name);

    public function getUser(): ?string;

    public function getPassword(): ?string;

    public function __toString(): string;
}
