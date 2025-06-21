# Mime

Classe utilitária para manipulação e verificação de tipos MIME no ecossistema PHPMX. Permite identificar, comparar e validar tipos de arquivos e extensões de forma prática.

```php
use PhpMx\Mime;
```

## Métodos principais

### getExMime

Retorna a extensão de um mimetype.

```php
Mime::getExMime(string $mime): ?string
```

### getMimeEx

Retorna o mimetype de uma extensão.

```php
Mime::getMimeEx(string $ex): ?string
```

### getMimeFile

Retorna o mimetype de um arquivo.

```php
Mime::getMimeFile(string $file): ?string
```

### checkMimeEx

Verifica se uma extensão corresponde a algum mimetype fornecido.

```php
Mime::checkMimeEx(string $ex, string ...$compare): bool
```

### checkMimeMime

Verifica se um mimetype corresponde a algum mimetype fornecido.

```php
Mime::checkMimeMime(string $mime, string ...$compare): bool
```

### checkMimeFile

Verifica se um arquivo corresponde a algum mimetype fornecido.

```php
Mime::checkMimeFile(string $file, string ...$compare): bool
```

## Exemplo de uso

```php
$mime = Mime::getMimeFile('documento.pdf');
$valido = Mime::checkMimeEx('pdf', 'application/pdf');
```
