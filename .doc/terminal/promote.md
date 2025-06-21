# phpmx promote

---

Promove (copia) um arquivo de um pacote ou dependência para o projeto atual, permitindo sobrescrever ou customizar arquivos padrão.

- Útil para customizar arquivos de templates, configurações ou scripts vindos de dependências.
- Não sobrescreve arquivos já existentes no projeto.
- O arquivo promovido passa a ser gerenciado localmente pelo projeto.

## Uso

```sh
phpmx promote <arquivo>
```

- `<arquivo>`: Caminho relativo do arquivo a ser promovido para o projeto.

Exemplo:

```sh
phpmx promote terminal/logo.php
```
