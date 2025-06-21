# Import

Classe utilitária para importação e manipulação de arquivos PHP no ecossistema PHPMX. Permite importar arquivos, obter variáveis, conteúdo, retorno e saída de scripts de forma flexível.

```php
use PhpMx\Import;
```

## Métodos principais

### only

Importa um arquivo PHP (com require ou require_once).

```php
Import::only(string $filePath, bool $once = true): bool
```

### content

Retorna o conteúdo de um arquivo, com suporte a preparação de variáveis.

```php
Import::content(string $filePath, string|array $prepare = []): string
```

### return

Retorna o resultado (return) de um arquivo PHP.

```php
Import::return(string $filePath, array $params = []): mixed
```

### var

Retorna o valor de uma variável definida dentro de um arquivo PHP.

```php
Import::var(string $filePath, string $varName, array $params = []): mixed
```

### output

Retorna a saída de texto gerada por um arquivo PHP.

```php
Import::output(string $filePath, array $params = []): string
```

## Exemplo de uso

```php
Import::only('config.php');
$conteudo = Import::content('config.php');
$retorno = Import::return('config.php');
$variavel = Import::var('config.php', 'MINHA_VARIAVEL');
$saida = Import::output('script.php');
```
