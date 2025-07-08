<?php

namespace Controller\Energize;

use Energize\Front;
use PhpMx\View;
use Throwable;

class Error
{
    function handleThrowable(Throwable $e)
    {
        $status = $e->getCode();
        $message = env("STM_$status") ?? 'Erro desconhecido';

        Front::title($message);
        Front::layout(null);

        return View::render('_base/error', [
            'status' => $status,
            'message' => $message,
        ]);
    }
}
