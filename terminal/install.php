<?php

use PhpMx\Dir;
use PhpMx\File;
use PhpMx\Import;
use PhpMx\Terminal;

return new class extends Terminal {

  function __invoke()
  {
    Dir::create('helper');
    Dir::create('helper/constant');
    Dir::create('helper/function');
    Dir::create('helper/script');
    Dir::create('source');
    Dir::create('storage');
    Dir::create('terminal');

    File::copy(path(dirname(__FILE__, 2), '.gitignore'), './.gitignore');
    File::copy(path(dirname(__FILE__, 2), 'helper/script/path.php'), './helper/script/path.php');

    $templateEnv = Import::content('storage/template/env.txt');
    File::create('./.env', $templateEnv);

    Terminal::run('composer');
  }
};
