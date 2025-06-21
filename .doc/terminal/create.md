# phpmx create

---

Grupo de comandos para criação de artefatos no projeto PHPMX. Os subcomandos disponíveis são:

- Os comandos criam arquivos prontos para uso, evitando sobrescrever arquivos já existentes.
- Útil para automação e padronização de novos recursos no projeto.

## create.cif

Cria um novo certificado CIF no diretório `storage/certificate`.

```sh
phpmx create cif <nome>
```

- `<nome>`: Nome do arquivo de certificado a ser criado (sem extensão).

Exemplo:

```sh
phpmx create cif empresa123
```

---

## create.command

Cria um novo comando customizado no diretório `terminal/` a partir de um template.

```sh
phpmx create command <nome>
```

- `<nome>`: Nome do comando a ser criado.

Exemplo:

```sh
phpmx create command relatorio
```

---

## create.install

Cria um script de instalação padrão no projeto.

```sh
phpmx create install
```
