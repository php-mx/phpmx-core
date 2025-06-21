# Json

Classe utilitária para importação e exportação de arquivos JSON no ecossistema PHPMX. Facilita a leitura e escrita de arrays em arquivos `.json` de forma simples e segura.

```php
use PhpMx\Json;
```

## Métodos principais

### import

Importa o conteúdo de um arquivo JSON para um array.

```php
Json::import(string $path): ?array
```

### export

Exporta um array para um arquivo JSON.

```php
Json::export(array $array, string $path, bool $merge = false): void
```

## Exemplo de uso

```php
Json::export(['nome' => 'PHPMX'], 'dados.json');
$dados = Json::import('dados.json');
```
