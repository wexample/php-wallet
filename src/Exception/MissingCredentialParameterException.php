<?php

declare(strict_types=1);

namespace Wexample\PhpWallet\Exception;

use InvalidArgumentException;

final class MissingCredentialParameterException extends InvalidArgumentException
{
    /**
     * @param list<string> $parameters The parameters it misses.
     */
    public function __construct(public readonly string $credentialName, public readonly array $parameters)
    {
        parent::__construct(sprintf("Credential '%s' misses: %s", $credentialName, implode(', ', $parameters)));
    }
}
