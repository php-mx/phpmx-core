# phpmx cif

---

Grupo de comandos para aplicar e remover cifra (criptografia simples) em conteúdos no PHPMX. Os subcomandos disponíveis são:

## cif.on

Aplica cifra a um conteúdo fornecido.

```sh
php mx cif on <conteúdo>
```

- `<conteúdo>`: Texto a ser cifrado.

Exemplo:

```sh
php mx cif on teste
```

---

## cif.off

Remove a cifra de um conteúdo cifrado.

```sh
php mx cif off <conteúdo_cifrado>
```

- `<conteúdo_cifrado>`: Texto cifrado a ser decifrado.

Exemplo:

```sh
php mx cif off -N6bYTvEcTviuktWMhL-
```
