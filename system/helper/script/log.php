<?php

use PhpMx\Log;

Log::start('LOG');

$composerLoader = spl_autoload_functions()[0];

foreach (spl_autoload_functions() as $loader) spl_autoload_unregister($loader);

spl_autoload_register(fn($class) => log_add('autoload', $class, fn() => $composerLoader($class)));
