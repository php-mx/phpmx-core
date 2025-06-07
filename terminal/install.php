<?php

use PhpMx\Dir;
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

    Terminal::run('composer');
  }
};
