<?php

declare(strict_types=1);

namespace Wexample\PhpWallet\Class;

/**
 * A parameter a type of credential declares: required unless said otherwise,
 * as wexample-wallet reads it. A secret is never shown back once saved; a
 * default fills it when left out; choices are the only values it takes.
 */
final readonly class CredentialParameter
{
    /**
     * @param list<string> $choices
     */
    public function __construct(
        public string $name,
        public ?string $label = null,
        public bool $required = true,
        public bool $secret = false,
        public ?string $default = null,
        public array $choices = [],
    ) {
    }

    /**
     * @param array{name: string, label?: string, required?: bool, secret?: bool, default?: string|int, choices?: list<string>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            label: $data['label'] ?? null,
            required: $data['required'] ?? true,
            secret: $data['secret'] ?? false,
            default: isset($data['default']) ? (string) $data['default'] : null,
            choices: array_values(array_map('strval', $data['choices'] ?? [])),
        );
    }

    public function getLabel(): string
    {
        return $this->label ?? $this->name;
    }
}
