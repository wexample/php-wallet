## A type

```php
$postgres = CredentialType::fromArray([
    'name' => 'postgres',
    'label' => 'PostgreSQL',
    'parameters' => [
        ['name' => 'host'],
        ['name' => 'port', 'default' => 5432],
        ['name' => 'database'],
        ['name' => 'user'],
        ['name' => 'password', 'secret' => true, 'required' => false],
    ],
]);
```

A parameter is required unless it says `required: false`, as `wexample-wallet` reads it; one with a `default` is never missing. `choices` lists the only values it takes. The array is the shape of the YAML file `wexample-wallet` reads a type from.

## The wallet

```php
$wallet = Wallet::fromArray([
    'open_ai' => ['type' => 'open_ai', 'api_key' => 'sk-...'],
    'prod-db' => ['type' => 'postgres', 'host' => 'db.example', 'database' => 'app', 'user' => 'app'],
], [$postgres]);

$wallet->get('prod-db')->get('host');           // by name
$wallet->getByType('open_ai')->get('api_key');  // the first of its type
$wallet->findByType('postgres');                // all of its type
```

A credential missing a required parameter of its type is refused when added (`MissingCredentialParameterException`); one whose type the wallet does not know is kept unchecked. Asking for a credential the wallet does not hold throws `CredentialNotFoundException`, which names what it holds.

## A credential

```php
$credential->get('port');              // its value, or MissingCredentialParameterException
$credential->get('port', '5432');      // or the default given
$credential->find('password');         // or null
$postgres->resolveParameters($credential); // its parameters, the type's defaults filling the gaps
```

Its parameters are secrets: `var_dump()` and `print_r()` show its name and type alone.
