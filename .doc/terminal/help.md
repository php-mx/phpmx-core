# phpmx help

---

Grupo de comandos para exibir informações de ajuda e estrutura do projeto PHPMX. Os subcomandos disponíveis são:

- Útil para explorar rapidamente comandos e arquivos do projeto.
- Ajuda a identificar comandos disponíveis e arquivos sobrescritos por dependências.

## help.command

Lista todos os comandos disponíveis no terminal, agrupando por origem (projeto ou pacotes).

```sh
php mx help command
```

- Exibe todos os comandos customizados e suas variações.
- Mostra exemplos de uso para cada comando.

---

## help.storage

Lista todos os arquivos presentes no diretório `storage` e suas origens.

```sh
php mx help storage
```

- Exibe todos os arquivos de storage, indicando se foram sobrescritos por pacotes.
