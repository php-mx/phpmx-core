<?php

namespace Controller\Base;

use PhpMx\Assets;
use PhpMx\File;
use PhpMx\Request;

class Download
{
    function default()
    {
        $file = path('storage/download', ...Request::route());

        if (!File::check($file))
            $file = path(CORE_PATH, $file);

        Assets::download($file);
    }
}
