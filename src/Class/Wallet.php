<?php

declare(strict_types=1);

namespace Wexample\PhpWallet\Class;

use Wexample\PhpWallet\Exception\CredentialNotFoundException;

/**
 * The credentials an application holds, handed to whatever needs one; the
 * twin of wexample-wallet. Built at startup — from a file, a secret manager,
 * a database —, nothing read from the environment behind its back.
 *
 *     $wallet = Wallet::fromArray([
 *         'open_ai' => ['type' => 'open_ai', 'api_key' => 'sk-...'],
 *         'prod-db' => ['type' => 'postgres', 'host' => '...', 'password' => '...'],
 *     ]);
 *     $wallet->get('prod-db')->get('host');
 *     $wallet->getByType('open_ai')->get('api_key');
 *
 * With credential types, a credential is checked when added: a wallet fails
 * when built rather than on first use.
 */
final class Wallet
{
    /** @var array<string, CredentialType> */
    private array $credentialTypes = [];

    /** @var array<string, Credential> */
    private array $credentials = [];

    /**
     * @param list<CredentialType> $credentialTypes
     */
    public function __construct(array $credentialTypes = [])
    {
        foreach ($credentialTypes as $credentialType) {
            $this->credentialTypes[$credentialType->name] = $credentialType;
        }
    }

    /**
     * @param array<string, array<string, string|int>> $data {name: {type: ..., <parameter>: <value>, ...}}
     * @param list<CredentialType> $credentialTypes
     */
    public static function fromArray(array $data, array $credentialTypes = []): self
    {
        $wallet = new self($credentialTypes);

        foreach ($data as $name => $values) {
            $parameters = $values;
            unset($parameters['type']);
            $wallet->add(new Credential((string) $name, (string) $values['type'], array_map('strval', $parameters)));
        }

        return $wallet;
    }

    public function add(Credential $credential): void
    {
        ($this->credentialTypes[$credential->type] ?? null)?->validate($credential);
        $this->credentials[$credential->name] = $credential;
    }

    public function has(string $name): bool
    {
        return isset($this->credentials[$name]);
    }

    public function get(string $name): Credential
    {
        return $this->credentials[$name] ?? throw CredentialNotFoundException::named($name, array_keys($this->credentials));
    }

    /** @return list<Credential> */
    public function findByType(string $type): array
    {
        return array_values(array_filter(
            $this->credentials,
            static fn (Credential $credential): bool => $credential->type === $type,
        ));
    }

    /** The first credential of that type, for a service used through a single account. */
    public function getByType(string $type): Credential
    {
        return $this->findByType($type)[0] ?? throw CredentialNotFoundException::typed(
            $type,
            array_values(array_unique(array_map(static fn (Credential $credential): string => $credential->type, $this->credentials))),
        );
    }

    /** @return array<string, Credential> */
    public function all(): array
    {
        return $this->credentials;
    }

    public function getCredentialType(string $type): ?CredentialType
    {
        return $this->credentialTypes[$type] ?? null;
    }

    /** @return array<string, CredentialType> */
    public function getCredentialTypes(): array
    {
        return $this->credentialTypes;
    }
}
