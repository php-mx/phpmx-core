# PhpMx\Code

# Code

Classe utilitária para codificação e verificação de strings, utilizada para gerar códigos únicos e protegidos a partir de valores diversos no ecossistema PHPMX.

```php
use PhpMx\Code;
```

## Métodos principais

### on

Gera um código protegido a partir de qualquer valor (string, número, objeto, etc).

```php
Code::on(mixed $var): string
```

### off

Recupera o hash MD5 original usado para gerar uma string codificada.

```php
Code::off(mixed $var): string
```

### check

Verifica se uma variável é uma string codificada válida.

```php
Code::check(mixed $var): bool
```

### compare

Verifica se todas as strings têm o mesmo código original (hash MD5).

```php
Code::compare(mixed $initial, mixed ...$compare): bool
```

## Exemplo de uso

```php
$codigo = Code::on('meu-valor');
$md5 = Code::off($codigo);
$valido = Code::check($codigo);
$iguais = Code::compare($codigo, Code::on('meu-valor'));
```

---

A chave de codificação é definida pela variável de ambiente

```
CODE = minhaChave
```
