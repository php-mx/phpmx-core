<?php

namespace Controller\MxServer;

use PhpMx\Path;
use PhpMx\Request;

class Download
{
    function __invoke()
    {
        $file = Path::seekForFile('library/download', ...Request::route());
        \PhpMx\Assets::download($file);
    }
}
