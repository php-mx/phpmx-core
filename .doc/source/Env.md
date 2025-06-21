# Env

Classe utilitária para gerenciamento de variáveis de ambiente no ecossistema PHPMX. Permite carregar, definir e recuperar valores de ambiente de forma simples e centralizada.

```php
use PhpMx\Env;
```

## Métodos principais

### loadFile

Carrega variáveis de ambiente de um arquivo para o sistema.

```php
Env::loadFile(string $filePath): bool
```

### set

Define o valor de uma variável de ambiente.

```php
Env::set(string $name, mixed $value): void
```

### get

Recupera o valor de uma variável de ambiente.

```php
Env::get(string $name): mixed
```

### default

Define variáveis de ambiente padrão caso não tenham sido declaradas.

```php
Env::default(string $name, mixed $value): void
```

## Exemplo de uso

```php
Env::loadFile('.env');
Env::set('APP_DEBUG', true);
$debug = Env::get('APP_DEBUG');
Env::default('APP_ENV', 'production');
```
