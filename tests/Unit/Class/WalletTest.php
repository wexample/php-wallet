<?php

declare(strict_types=1);

namespace Wexample\PhpWallet\Tests\Unit\Class;

use PHPUnit\Framework\TestCase;
use Wexample\PhpWallet\Class\Credential;
use Wexample\PhpWallet\Class\CredentialType;
use Wexample\PhpWallet\Class\Wallet;
use Wexample\PhpWallet\Exception\CredentialNotFoundException;
use Wexample\PhpWallet\Exception\MissingCredentialParameterException;

class WalletTest extends TestCase
{
    private function postgres(): CredentialType
    {
        return CredentialType::fromArray([
            'name' => 'postgres',
            'label' => 'PostgreSQL',
            'parameters' => [
                ['name' => 'host'],
                ['name' => 'port', 'default' => 5432],
                ['name' => 'user'],
                ['name' => 'password', 'secret' => true, 'required' => false],
            ],
        ]);
    }

    public function testCredentialsAreFoundByNameAndByType(): void
    {
        $wallet = Wallet::fromArray([
            'open_ai' => ['type' => 'open_ai', 'api_key' => 'sk-test'],
            'prod-db' => ['type' => 'postgres', 'host' => 'db.test', 'user' => 'app', 'port' => 5433],
        ], [$this->postgres()]);

        self::assertSame('db.test', $wallet->get('prod-db')->get('host'));
        self::assertSame('5433', $wallet->get('prod-db')->get('port'));
        self::assertSame('sk-test', $wallet->getByType('open_ai')->get('api_key'));
        self::assertTrue($wallet->has('prod-db'));
        self::assertCount(1, $wallet->findByType('postgres'));
    }

    public function testACredentialMissingARequiredParameterIsRefusedWhenAdded(): void
    {
        $wallet = new Wallet([$this->postgres()]);

        $this->expectException(MissingCredentialParameterException::class);
        $this->expectExceptionMessage("Credential 'prod-db' misses: user");
        $wallet->add(new Credential('prod-db', 'postgres', ['host' => 'db.test']));
    }

    public function testATypeUnknownToTheWalletIsNotChecked(): void
    {
        $wallet = new Wallet([$this->postgres()]);
        $wallet->add(new Credential('mail', 'smtp'));

        self::assertTrue($wallet->has('mail'));
    }

    public function testMissingCredentialsSayWhatTheWalletHolds(): void
    {
        $wallet = Wallet::fromArray(['b' => ['type' => 'smtp'], 'a' => ['type' => 'open_ai']]);

        try {
            $wallet->get('c');
            self::fail();
        } catch (CredentialNotFoundException $exception) {
            self::assertStringContainsString("named 'c'", $exception->getMessage());
            self::assertStringContainsString('available: a, b', $exception->getMessage());
        }

        $this->expectException(CredentialNotFoundException::class);
        $this->expectExceptionMessage("of type 'postgres'");
        $wallet->getByType('postgres');
    }

    public function testDefaultsFillWhatACredentialLeavesOut(): void
    {
        $type = $this->postgres();
        $credential = new Credential('prod-db', 'postgres', ['host' => 'db.test', 'user' => 'app']);

        self::assertSame([], $type->getMissingParameters($credential));
        self::assertSame('5432', $type->resolveParameters($credential)['port']);
        self::assertTrue($type->isSecret('password'));
        self::assertFalse($type->isSecret('host'));
        self::assertFalse($type->isSecret('unknown'));
    }

    public function testAParameterAskedWithoutDefaultMustBeThere(): void
    {
        $credential = new Credential('prod-db', 'postgres', ['host' => 'db.test']);

        self::assertSame('fallback', $credential->get('user', 'fallback'));
        self::assertNull($credential->find('user'));
        $this->expectException(MissingCredentialParameterException::class);
        $credential->get('user');
    }

    public function testADumpShowsNoSecret(): void
    {
        $dump = print_r(new Credential('prod-db', 'postgres', ['password' => 'hunter2']), true);

        self::assertStringNotContainsString('hunter2', $dump);
    }
}
