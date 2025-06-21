# Cif

Classe utilitária para cifra e decifra de variáveis, utilizada para proteger e validar dados no ecossistema PHPMX.

```php
use PhpMx\Cif;
```

## Métodos principais

### on

Cifra uma variável, retornando uma string protegida.

```php
Cif::on(mixed $var, ?string $charKey = null): string
```

### off

Decifra uma string cifrada, retornando o valor original.

```php
Cif::off(mixed $var): mixed
```

### check

Verifica se uma variável atende os requisitos para ser uma cifra.

```php
Cif::check(mixed $var): bool
```

### compare

Verifica se todas as variáveis têm a mesma cifra (conteúdo original igual).

```php
Cif::compare(mixed $initial, mixed ...$compare): bool
```

## Exemplo de uso

```php
$cifrado = Cif::on('meu-segredo');
$original = Cif::off($cifrado);
$valido = Cif::check($cifrado);
$iguais = Cif::compare($cifrado, Cif::on('meu-segredo'));
```

---

A cifra utiliza um arquivo de certificado localizado em `storage/certificate/` para gerar a chave de proteção. Caso não exista, utiliza o arquivo `base.crt` padrão.

> **Atenção:**
>
> - O certificado é um arquivo sensível e deve ser guardado em local seguro.
> - Por padrão, arquivos em `storage/certificate/` são incluídos no `.gitignore` e não vão para o repositório.
> - Faça backup do seu certificado! Sem ele, não será possível decifrar dados cifrados anteriormente.

Para criar seu próprio arquivo de certificado utilize o comando:

```
php mx create.cif meuCertificado
```

Após isso, configure seu `.env` para:

```
CIF = meuCertificado
```
