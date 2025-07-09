<?php

namespace Controller\Base;

use PhpMx\Assets;
use PhpMx\Path;
use PhpMx\Request;

class Download
{
    function default()
    {
        $file = Path::seekForFile('library/download', ...Request::route());
        Assets::download($file);
    }
}
