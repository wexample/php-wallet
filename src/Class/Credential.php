<?php

declare(strict_types=1);

namespace Wexample\PhpWallet\Class;

use Wexample\PhpWallet\Exception\MissingCredentialParameterException;

/**
 * Named access to a service: its type says which parameters it carries, an
 * `open_ai` credential an `api_key`, a `postgres` one a host, a port, a
 * database, a user and a password. Its parameters are secrets: a dump or a
 * trace shows its name and type alone.
 */
final readonly class Credential
{
    /**
     * @param array<string, string> $parameters
     */
    public function __construct(
        public string $name,
        public string $type,
        public array $parameters = [],
    ) {
    }

    public function has(string $parameter): bool
    {
        return isset($this->parameters[$parameter]);
    }

    /**
     * Its value; the default given when it has none, else a
     * MissingCredentialParameterException.
     */
    public function get(string $parameter, ?string $default = null): string
    {
        return $this->parameters[$parameter]
            ?? $default
            ?? throw new MissingCredentialParameterException($this->name, [$parameter]);
    }

    public function find(string $parameter): ?string
    {
        return $this->parameters[$parameter] ?? null;
    }

    /** @return array{name: string, type: string} */
    public function __debugInfo(): array
    {
        return ['name' => $this->name, 'type' => $this->type];
    }
}
