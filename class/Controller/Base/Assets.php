<?php

namespace Controller\Base;

use PhpMx\Path;
use PhpMx\Request;

class Assets
{
    function default()
    {
        $file = Path::seekForFile('library/assets', ...Request::route());
        \PhpMx\Assets::send($file);
    }
}
