# Dir

Classe utilitária para manipulação de diretórios no ecossistema PHPMX. Permite criar, remover, copiar, mover e vasculhar diretórios de forma simples e segura.

```php
use PhpMx\Dir;
```

## Métodos principais

### create

Cria um diretório (e subdiretórios, se necessário).

```php
Dir::create(string $path): ?bool
```

### remove

Remove um diretório. Pode ser recursivo.

```php
Dir::remove(string $path, bool $recursive = false): ?bool
```

### copy

Copia um diretório para outro local.

```php
Dir::copy(string $path_from, string $path_to, bool $replace = false): ?bool
```

### move

Move um diretório para outro local.

```php
Dir::move(string $path_from, string $path_to): ?bool
```

### seekForFile

Vasculha um diretório em busca de arquivos.

```php
Dir::seekForFile(string $path, bool $recursive = false): array
```

### seekForDir

Vasculha um diretório em busca de subdiretórios.

```php
Dir::seekForDir(string $path, bool $recursive = false): array
```

### seekForAll

Vasculha um diretório em busca de arquivos e subdiretórios.

```php
Dir::seekForAll(string $path, bool $recursive = false): array
```

### getOnly

Retorna o caminho do diretório, removendo referência a arquivos.

```php
Dir::getOnly(string $path): string
```

### check

Verifica se um diretório existe.

```php
Dir::check(string $path): bool
```

## Exemplo de uso

```php
Dir::create('storage/teste');
Dir::copy('storage/teste', 'storage/backup');
$arquivos = Dir::seekForFile('storage/teste');
Dir::remove('storage/teste', true);
```
