<?php

declare(strict_types=1);

namespace Wexample\PhpWallet\Exception;

use OutOfBoundsException;

/** No credential with the name, or of the type, asked for. */
final class CredentialNotFoundException extends OutOfBoundsException
{
    /**
     * @param list<string> $available What the wallet holds: credential names, or their types when a type was asked for.
     */
    private function __construct(string $wanted, public readonly array $available)
    {
        sort($available);
        parent::__construct(sprintf(
            'No credential %s in the wallet (available: %s); add one to the wallet built at startup',
            $wanted,
            implode(', ', $available) ?: 'none',
        ));
    }

    /** @param list<string> $available */
    public static function named(string $name, array $available): self
    {
        return new self(sprintf("named '%s'", $name), $available);
    }

    /** @param list<string> $available */
    public static function typed(string $type, array $available): self
    {
        return new self(sprintf("of type '%s'", $type), $available);
    }
}
