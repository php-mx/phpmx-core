# File

Classe utilitária para manipulação de arquivos no ecossistema PHPMX. Permite criar, remover, copiar, mover e obter informações de arquivos de forma simples e segura.

```php
use PhpMx\File;
```

## Métodos principais

### create

Cria um arquivo de texto.

```php
File::create(string $path, string $content, bool $recreate = false): ?bool
```

### remove

Remove um arquivo.

```php
File::remove(string $path): ?bool
```

### copy

Copia um arquivo para outro local.

```php
File::copy(string $path_from, string $path_to, bool $replace = false): ?bool
```

### move

Move um arquivo para outro local.

```php
File::move(string $path_from, string $path_to, bool $replace = false): ?bool
```

### getOnly

Retorna apenas o nome do arquivo com a extensão.

```php
File::getOnly(string $path): string
```

### getName

Retorna apenas o nome do arquivo (sem extensão).

```php
File::getName(string $path): string
```

### getEx

Retorna apenas a extensão do arquivo.

```php
File::getEx(string $path): string
```

### setEx

Define ou altera a extensão de um arquivo.

```php
File::setEx(string $path, string $extension = 'php'): string
```

### check

Verifica se um arquivo existe.

```php
File::check(string $path): bool
```

### getSize

Retorna o tamanho do arquivo (em formato humano ou bytes).

```php
File::getSize($path, $human = true): int|string
```

### getLastModified

Retorna a data de modificação do arquivo (timestamp).

```php
File::getLastModified($path): ?int
```

## Exemplo de uso

```php
File::create('storage/teste.txt', 'conteúdo');
$tamanho = File::getSize('storage/teste.txt');
File::remove('storage/teste.txt');
```
