# phpmx composer

---

Atualiza o autoload do `composer.json` com os helpers do projeto e executa o comando Composer adequado ao ambiente (dev ou produção).

- Útil para garantir que todas as funções helpers estejam disponíveis via autoload.
- Recomenda-se rodar após adicionar novos helpers ou alterar estrutura de pastas.
- Exibe mensagens no terminal sobre o progresso e comandos executados.

## Uso

```sh
php mx composer
```

- Atualiza o arquivo `composer.json` para garantir que todos os helpers estejam no autoload.
- Executa `composer install` (em ambiente de desenvolvimento) ou `composer install --no-dev --optimize-autoloader` (em produção).

## Parâmetros

- `forceDev` (opcional): Se informado e verdadeiro, força a execução do modo desenvolvimento.

## Exemplo

```sh
php mx composer
php mx composer 1   # força modo dev
```
