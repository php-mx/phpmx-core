<?php

use PhpMx\Env;

Env::loadFile('./.env');
Env::loadFile('./.conf');

/** Define se o sistema está em modo de desenvolvimento (exibe erros detalhados) */
Env::default('DEV', false);

/** Define o certificado padrão utilizado pelo motor de criptografia Cif */
Env::default('CIF', 'base');

/** Chave de segurança utilizada para a geração de hashes MX5 */
Env::default('MX5_KEY', 'mx5key');

/** Habilita ou desabilita o armazenamento de cache em arquivos físicos */
Env::default('USE_CACHE_FILE', true);
