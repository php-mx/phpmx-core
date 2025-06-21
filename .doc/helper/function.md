# Funções

Este documento lista as funções globais utilitárias disponíveis no PHPMX, definidas em `/helper/function`.

---

## cache

Armazena e recupera o retorno de uma Closure em `/storage/cache`

```php
cache(string $cacheName, Closure $action): mixed
```

---

## applyChanges

Aplica mudanças em um array

```php
applyChanges(&$array, $changes): void
```

## getChanges

Retorna as mudanças realizadas em um array

```php
getChanges($changed, $original): array
```

---

## dbug

Realiza o var_dump de variáveis

```php
dbug(mixed ...$params): void
```

## dbugpre

Realiza o var_dump de variáveis dentro de uma tag HTML pre

```php
dbugpre(mixed ...$params): void
```

## dd

Realiza o var_dump de variáveis finalizando o sistema

```php
dd(mixed ...$params): never
```

## ddpre

Realiza o var_dump de variáveis dentro de uma tag HTML pre finalizando o sistema

```php
ddpre(mixed ...$params): never
```

---

## env

Recupera o valor de uma variável de ambiente

```php
env(string $name): mixed
```

---

## is_class

Verifica se um objeto é ou extende uma classe

```php
is_class(mixed $object, object|string $class): bool
```

## is_extend

Verifica se um objeto extende uma classe

```php
is_extend(mixed $object, object|string $class): bool
```

## is_implement

Verifica se um objeto implementa uma interface

```php
is_implement(mixed $object, object|string $interface): bool
```

## is_trait

Verifica se um objeto utiliza uma trait

```php
is_trait(mixed $object, object|string|null $trait): bool
```

## is_blank

Verifica se uma variável é nula, vazia ou composta de espaços em branco

```php
is_blank(mixed $var): bool
```

## is_md5

Verifica se uma variável é hash MD5

```php
is_md5(mixed $var): bool
```

## is_json

Verifica se uma variável é uma string JSON

```php
is_json(mixed $var): bool
```

## is_closure

Verifica se uma variável é uma função anônima ou objeto callable

```php
is_closure(mixed $var): bool
```

## is_stringable

Verifica se uma variável é uma string ou algo que possa ser convertido para string

```php
is_stringable(mixed $var): bool
```

## is_base64

Verifica se uma variável é uma string base64

```php
is_base64(mixed $var): bool
```

## is_image_base64

Verifica se uma variável é uma url de imagem base64

```php
is_image_base64(mixed $var): bool
```

## is_serialized

Verifica se uma variável corresponde a uma string serializada

```php
is_serialized($var, $strict = true): bool
```

---

## log_add

Adicona ao log uma linha ou um escopo de linhas

```php
log_add(string $type, string $message, array $prepare = [], ?Closure $scope = null): mixed
```

## log_exception

Adiciona uma linha de exceção ao log

```php
log_exception(Exception | Error $e)
```

---

## num_format

Formata um número em float

```php
num_format(int|float|string $number, int $decimals = 2, int $roundType = -1): float
```

## num_round

Arredonda um número

```php
num_round(int|float|string $number, int $roundType = 0): int
```

## num_interval

Garante que um número esteja dentro de um intervalo

```php
num_interval(int|float|string $number, int|float|string $min = 0, int|float|string $max = 0): int|float
```

## num_positive

Retorna o representativo positivo de um número

```php
num_positive(int|float|string $number): int|float
```

## num_negative

Retorna o representativo negativo de um número

```php
num_negative(int|float|string $number): int|float
```

---

## path

Formata um caminho de diretório

```php
path(): string
```

---

## prepare

Prepara um texto para ser exibido substituindo ocorrências do template

```php
prepare(?string $string, array|string $prepare = []): string
```

---

## remove_accents

Remove a acentuação de uma string

```php
remove_accents(string $string): string
```

---

## strToCamelCase

Converte uma string para camelCase

```php
strToCamelCase(string $str): string
```

## strToKebabCase

Converte uma string para kebab-case

```php
strToKebabCase(string $str): string
```

## strToPascalCase

Converte uma string para PascalCase

```php
strToPascalCase(string $str): string
```

---

## str_get_var

Extrai uma variável de dentro de uma string

```php
str_get_var($var): mixed
```

## str_replace_all

Substitui todas as ocorrências da string de pesquisa pela string de substituição

```php
str_replace_all(array|string $search, array|string $replace, string $subject, int $loop = 10): string
```

## str_replace_first

Substitui a primeira ocorrência da string de pesquisa pela string de substituição

```php
str_replace_first(array|string $search, array|string $replace, string $subject): string
```

## str_replace_last

Substitui a última ocorrência da string de pesquisa pela string de substituição

```php
str_replace_last(array|string $search, array|string $replace, string $subject): string
```

## str_trim

Tira o espaço em branco (ou outros caracteres) do início e do fim de uma substring dentro de uma string

```php
str_trim(string $string, array|string $substring, array|string $characters = " \t\n\r\0\x0B"): string
```

## mb_str_replace

Substitui ocorrências da string de pesquisa pela string de substituição (multibyte)

```php
mb_str_replace(array|string $search, array|string $replace, string $subject, &$count = 0): string
```

## mb_str_replace_all

Substitui todas as ocorrências da string de procura com a string de substituição (multibyte)

```php
mb_str_replace_all(array|string $search, array|string $replace, string $subject, int $loop = 10): string
```

## mb_str_split

Converte uma string em um array (multibyte)

```php
mb_str_split(string $string, int $string_length = 1): array
```

---

## uuid

Gera uma string de id única

```php
uuid(): string
```
