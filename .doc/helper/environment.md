# Variáveis de ambiente

Este documento lista as variáveis de ambiente padrão do PHPMX. Todas podem ser sobrescritas no seu próprio arquivo `.env` ou via configuração manual.

- **DEV** (padrão: `false`): Indica se o ambiente está em modo de desenvolvimento. Ativa logs detalhados e recursos extras para debugging.
- **CIF** (padrão: `base`): Nome do arquivo de certificado `.crt` utilizado para cifragem e decifragem de dados sensíveis.
- **CODE** (padrão: `mxcodekey`): Senha utilizada para operações de codificação e decodificação de dados.
