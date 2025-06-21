# PHPMX - CORE

O PHPMX **não é um framework**, nem um pacote genérico que você injeta em qualquer projeto. Ele é o **núcleo conceitual e estrutural do ecossistema PHPMX**. Ao adotá-lo, você não está apenas instalando código — você está assumindo um compromisso com uma forma diferente de programar.

A filosofia do PHPMX parte de três princípios fundamentais:

1. **PHP como linguagem, não como ferramenta auxiliar**
   O foco é no domínio pleno da linguagem, não em abstrações que escondem sua essência.

2. **Autonomia do programador**
   O sistema não impõe estrutura, design pattern ou convenção. Ele fornece ferramentas; você define a arquitetura.

3. **Controle absoluto**
   Zero mágica, zero acoplamento implícito. Tudo é explícito, extensível e rastreável.

---

## Instalação

A instalação é feita em um projeto **vazio**, utilizando apenas dois comandos no terminal:

```bash
composer require phpmx/core
.\vendor\bin\mx install
```

Você pode verificar se tudo está pronto executando o comando abaixo:

```bash
php mx
```

---

## Estrutura de pastas

Existem 4 pastas principais no PHPMX-CORE:

- **helper**: Arquivos globais do projeto — funções, constantes e scripts iniciais.
- **source**: Classes PHP mapeadas via Composer (PSR-4).
- **storage**: Arquivos persistentes do sistema.
- **terminal**: Comandos executáveis via CLI.

### helper

Diretório onde ficam os arquivos carregados globalmente no projeto. Possui três subpastas:

- **constant**: Constantes do projeto.
- **function**: Funções utilitárias globais.
- **script**: Scripts de inicialização e pré-configuração.

Após criar os arquivos, atualize o `composer.json` para incluí-los na seção `files`. Você pode fazer isso automaticamente com:

```bash
php mx composer
```

Depois disso, os arquivos estarão disponíveis globalmente — **sem necessidade de `include` ou `require`**.

### source

Diretório de classes mapeadas via PSR-4.
Crie seus arquivos respeitando o padrão de namespace. O Composer vai incluí-los automaticamente conforme forem utilizados.

```text
\source\MeuNamespace\MinhaClasse.php

new \MeuNamespace\MinhaClasse();
```

### storage

Armazena arquivos persistentes do seu sistema. Você pode acessá-los via caminho relativo:

```text
.\storage\image\image1.jpg
.\storage\certificate\mycertificate.crt
```

### terminal

Contém os arquivos de comando expostos no terminal.

Para executar comandos, use o arquivo `mx` na raiz do projeto:

```bash
php mx <comando> <...parâmetros>
```

Cada comando executa um arquivo correspondente dentro de `terminal`.

Você pode criar seus próprios comandos com:

```bash
php mx create.command teste
```

E executá-lo com:

```bash
php mx teste
```

---

## Documentação

- **Helper**

  - [constant](./.doc/helper/constant.md)
  - [function](./.doc/helper/function.md)

- **Souce**

  - [Cif](./.doc/source/cif.md)
  - [Code](./.doc/source/code.md)
  - [Dir](./.doc/source/dir.md)
  - [Env](./.doc/source/env.md)
  - [File](./.doc/source/file.md)
  - [Import](./.doc/source/import.md)
  - [Json](./.doc/source/json.md)
  - [Log](./.doc/source/log.md)
  - [Mime](./.doc/source/mime.md)
  - [Path](./.doc/source/path.md)
  - [Prepare](./.doc/source/prepare.md)
  - [Terminal](./.doc/source/terminal.md)

- **Terminal**

  - [cif](./doc/terminal/cif.md)
  - [code](./doc/terminal/code.md)
  - [create](./doc/terminal/create.md)
  - [help](./doc/terminal/help.md)
  - [composer](./doc/terminal/composer.md)
  - [install](./doc/terminal/install.md)
  - [promote](./doc/terminal/promote.md)
