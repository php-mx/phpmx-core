<?php

namespace Controller\Base;

use PhpMx\File;
use PhpMx\Request;

class Assets
{
    function default()
    {
        $file = path('storage/assets', ...Request::route());

        if (!File::check($file))
            $file = path(CORE_PATH, $file);

        \PhpMx\Assets::send($file);
    }
}
