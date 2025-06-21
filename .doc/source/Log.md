# Log

Classe utilitária para registro e manipulação de logs no ecossistema PHPMX. Permite iniciar, capturar, formatar e recuperar logs de execução de forma estruturada.

```php
use PhpMx\Log;
```

## Métodos principais

### get

Retorna o log atual com contadores.

```php
Log::get(): array
```

### getArray

Retorna o log em forma de array.

```php
Log::getArray(): array
```

### getString

Retorna o log em forma de string.

```php
Log::getString(): string
```

## Exemplo de uso

```php
$log = Log::get();
$logArray = Log::getArray();
$logString = Log::getString();
```
