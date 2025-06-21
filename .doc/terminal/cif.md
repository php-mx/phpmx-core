# phpmx cif

---

Grupo de comandos para aplicar e remover cifra (criptografia simples) em conteúdos no PHPMX. Os subcomandos disponíveis são:

## cif.on

Aplica cifra a um conteúdo fornecido.

```sh
phpmx cif on <conteúdo>
```

- `<conteúdo>`: Texto a ser cifrado.

Exemplo:

```sh
phpmx cif on teste
```

---

## cif.off

Remove a cifra de um conteúdo cifrado.

```sh
phpmx cif off <conteúdo_cifrado>
```

- `<conteúdo_cifrado>`: Texto cifrado a ser decifrado.

Exemplo:

```sh
phpmx cif off -N6bYTvEcTviuktWMhL-
```
