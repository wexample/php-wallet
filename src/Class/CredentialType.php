<?php

declare(strict_types=1);

namespace Wexample\PhpWallet\Class;

use Wexample\PhpWallet\Exception\MissingCredentialParameterException;

/**
 * The parameters a kind of credential carries, declared the way
 * wexample-wallet reads them:
 *
 *     name: postgres
 *     label: PostgreSQL
 *     parameters:
 *       - name: host
 *       - name: port
 *         default: 5432
 *       - name: password
 *         secret: true
 */
final readonly class CredentialType
{
    /** @var array<string, CredentialParameter> */
    public array $parameters;

    /**
     * @param list<CredentialParameter> $parameters
     */
    public function __construct(
        public string $name,
        public ?string $label = null,
        array $parameters = [],
    ) {
        $byName = [];
        foreach ($parameters as $parameter) {
            $byName[$parameter->name] = $parameter;
        }
        $this->parameters = $byName;
    }

    /**
     * @param array{name: string, label?: string, parameters?: list<array>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            label: $data['label'] ?? null,
            parameters: array_map(CredentialParameter::fromArray(...), $data['parameters'] ?? []),
        );
    }

    public function getLabel(): string
    {
        return $this->label ?? $this->name;
    }

    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    public function getParameter(string $name): ?CredentialParameter
    {
        return $this->parameters[$name] ?? null;
    }

    public function isSecret(string $name): bool
    {
        return true === ($this->parameters[$name] ?? null)?->secret;
    }

    /** @return list<string> */
    public function getMissingParameters(Credential $credential): array
    {
        return array_values(array_map(
            static fn (CredentialParameter $parameter): string => $parameter->name,
            array_filter(
                $this->parameters,
                static fn (CredentialParameter $parameter): bool => $parameter->required
                    && null === $parameter->default
                    && ! $credential->has($parameter->name),
            )
        ));
    }

    public function validate(Credential $credential): void
    {
        if ($missing = $this->getMissingParameters($credential)) {
            throw new MissingCredentialParameterException($credential->name, $missing);
        }
    }

    /**
     * The credential's parameters, the defaults filling what it leaves out.
     *
     * @return array<string, string>
     */
    public function resolveParameters(Credential $credential): array
    {
        $resolved = $credential->parameters;
        foreach ($this->parameters as $name => $parameter) {
            if (! isset($resolved[$name]) && null !== $parameter->default) {
                $resolved[$name] = $parameter->default;
            }
        }

        return $resolved;
    }
}
