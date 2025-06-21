# Prepare

Classe utilitária para preparação dinâmica de textos, substituindo templates e tags customizadas por valores fornecidos. Permite manipulação avançada de placeholders, útil para geração de mensagens, templates e automação de textos no PHPMX.

```php
use PhpMx\Prepare;
```

---

## Métodos principais

### prepare

Prepara um texto substituindo tags (placeholders) por valores do array informado.

```php
Prepare::prepare(?string $string, array|string $prepare = []): string
```

- **$string**: Texto base com tags do tipo `[#[tag]]`.
- **$prepare**: Array ou string com valores para substituir as tags.

**Exemplo:**

```php
Prepare::prepare('Olá, [#nome]!', ['nome' => 'Andre']); // Olá, Andre!
```

---

### tags

Retorna as tags (placeholders) encontradas em uma string.

```php
Prepare::tags($string): array
```

**Exemplo:**

```php
Prepare::tags('Olá, [#nome]! Seu código: [#codigo]'); // ['nome', 'codigo']
```

---

### keys

Retorna as chaves disponíveis em um array de prepare.

```php
Prepare::keys($prepare): array
```

**Exemplo:**

```php
Prepare::keys(['nome' => 'Andre', 'idade' => 30]); // ['nome', 'idade']
```

---

### scape

Escapa as tags prepare de um texto, evitando substituição.

```php
Prepare::scape($string, ?array $prepare = null): string
```

**Exemplo:**

```php
Prepare::scape('Olá, [#nome]!'); // Olá, [&#35nome]!
```

---

## Observações importantes

- Tags são do tipo `[#[tag]]` e podem ser sequenciais ou nomeadas.
- Suporta funções como valor de tag (closures).
- Permite parâmetros em tags, ex: `[#[funcao:param1,param2]]`.
- Útil para templates de e-mail, mensagens automáticas, logs e geração dinâmica de conteúdo.
- Para uso seguro, sempre valide os dados de entrada ao gerar textos dinâmicos.

---

## Exemplo avançado

```php
$template = 'Olá, [#nome]! Seu código é [#codigo].';
$dados = ['nome' => 'Andre', 'codigo' => 1234];
echo Prepare::prepare($template, $dados); // Olá, Andre! Seu código é 1234.
```
