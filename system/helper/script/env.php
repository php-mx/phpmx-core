<?php

use PhpMx\Env;

Env::loadFile('./.env');
Env::loadFile('./.conf');

Env::default('DEV', false);

Env::default('CIF', 'base');

Env::default('MX5_KEY', 'mx5key');

Env::default('USE_CACHE_FILE', true);
