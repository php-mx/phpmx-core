# Path

Classe utilitária para manipulação e busca de caminhos de arquivos e diretórios no ecossistema PHPMX. Permite formatar, registrar e localizar arquivos e pastas de forma flexível.

```php
use PhpMx\Path;
```

## Métodos principais

### format

Formata um caminho de diretório.

```php
Path::format(...$args): string
```

### registred

Retorna os caminhos registrados em path.

```php
Path::registred(): array
```

### seekFile

Busca e retorna um arquivo utilizando os caminhos registrados.

```php
Path::seekFile(...$args): ?string
```

### seekFiles

Busca e retorna todos os arquivos utilizando os caminhos registrados.

```php
Path::seekFiles(...$args): array
```

### seekDir

Busca e retorna um diretório utilizando os caminhos registrados.

```php
Path::seekDir(...$args): ?string
```

### seekDirs

Busca e retorna todos os diretórios utilizando os caminhos registrados.

```php
Path::seekDirs(...$args): array
```

## Exemplo de uso

```php
$caminho = Path::seekFile('meuarquivo.txt');
```
