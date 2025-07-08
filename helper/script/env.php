<?php

use PhpMx\Env;

Env::loadFile('./.env');
Env::loadFile('./.conf');

Env::default('DEV', false);

Env::default('CIF', 'base');

Env::default('CODE', 'mxcodekey');

Env::default('DB_MAIN_TYPE', 'sqlite');


Env::default('FORCE_SSL', true);

Env::default('TERMINAL_URL', 'http://localhost:8888');

Env::default('JWT', 'jwt-key');

Env::default('CACHE', null);
Env::default('CACHE_JS', '+30 days');
Env::default('CACHE_CSS', '+30 days');
Env::default('CACHE_ICO', '+30 days');
Env::default('CACHE_PNG', '+30 days');
Env::default('CACHE_JPG', '+30 days');
Env::default('CACHE_BMP', '+30 days');
Env::default('CACHE_GIF', '+30 days');
Env::default('CACHE_WEBP', '+30 days');
Env::default('CACHE_MP3', '+30 days');
Env::default('CACHE_MP4', '+30 days');

Env::default('STM_200', 'ok');
Env::default('STM_201', 'criado');
Env::default('STM_204', 'sem conteúdo');
Env::default('STM_303', 'redirecionamento');
Env::default('STM_400', 'requisição inválida');
Env::default('STM_401', 'não autorizado');
Env::default('STM_403', 'proibido');
Env::default('STM_404', 'não encontrado');
Env::default('STM_405', 'método não permitido');
Env::default('STM_500', 'erro interno do servidor');
Env::default('STM_501', 'não implementado');
Env::default('STM_503', 'serviço indisponível');

Env::default('COOKIE_LIFE', '+30 days');
