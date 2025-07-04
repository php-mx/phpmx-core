<?php

namespace Controller\Base;

use PhpMx\Energize\Front;
use PhpMx\View;
use Throwable;

class Error
{
    static function handlePageThrowable(Throwable $e)
    {
        $status = $e->getCode();
        $message = env("STM_$status") ?? 'Erro desconhecido';

        Front::title($message);
        Front::layout(null);

        return View::render('_base/error', ['status' => $status, 'message' => $message]);
    }
}
