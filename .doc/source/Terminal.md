# Terminal

Classe utilitária para execução de comandos de terminal no ecossistema PHPMX. Permite rodar comandos customizados, scripts de instalação e exibir saídas formatadas no terminal, integrando com o sistema de logs e helpers do framework.

```php
use PhpMx\Terminal;
```

---

## Métodos principais

### run

Executa um comando de terminal do PHPMX.

```php
Terminal::run(...$commandLine)
```

- **$commandLine**: Um ou mais parâmetros representando o comando e seus argumentos.
- Retorna o resultado da execução do comando.

**Exemplo:**

```php
Terminal::run('help');
Terminal::run('+install'); // Mostra logs detalhados se em modo DEV
```

---

### echo

Exibe uma linha de texto no terminal, com suporte a placeholders.

```php
Terminal::echo(string $line = '', string|array $prepare = []): void
```

**Exemplo:**

```php
Terminal::echo('Olá, [#nome]!', ['nome' => 'Andre']);
```

---

### echoLine

Exibe uma linha de separação no terminal.

```php
Terminal::echoLine(): void
```

**Exemplo:**

```php
Terminal::echoLine();
// Saída: ------------------------------------------------------------
```

---

## Observações importantes

- Comandos são buscados no diretório `terminal/` e devem ser classes que estendem `Terminal`.
- O método `run` aceita múltiplos argumentos, que são repassados ao comando.
- O prefixo `+` no comando ativa exibição de logs detalhados em modo DEV.
- O comando especial `--install` executa scripts de instalação de todos os pacotes registrados.
- Integra com o sistema de logs e helpers do PHPMX.
- Exceções e erros são tratados e exibidos no terminal, com detalhes do arquivo e linha.

---

## Exemplo avançado

```php
// Executando um comando customizado
Terminal::run('create', 'cif', 'meu-cnpj');

// Exibindo mensagem formatada
Terminal::echo('Bem-vindo, [#usuario]!', ['usuario' => 'Andre']);

// Linha de separação
Terminal::echoLine();
```
