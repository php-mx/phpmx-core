<?php

use PhpMx\Env;

Env::loadFile('./.env');
Env::loadFile('./.conf');

Env::default('DEV', false);

Env::default('CIF', 'base');
Env::default('CODE', 'codekey');
