<?php

use PhpMx\Env;

Env::loadFile('./.env');
Env::loadFile('./.conf');

Env::default('DEV', true);

Env::default('CIF', 'base');
Env::default('CODE', 'codekey');
Env::default('JWT', 'jwt-key');
