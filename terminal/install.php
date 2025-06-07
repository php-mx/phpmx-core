<?php

use PhpMx\Dir;
use PhpMx\File;
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

    File::copy(path(dirname(__DIR__, 2), '.gitignore'), './.gitignore');
    File::copy(path(dirname(__DIR__, 2), 'helper/script/path.php'), './helper/script/path.php');

    Terminal::run('composer');
  }
};
