<?php

namespace Controller\Base;

use PhpMx\Page;
use Throwable;

class Error
{
    static function handlePageThrowable(Throwable $e)
    {
        $status = $e->getCode();
        $message = env("STM_$status") ?? 'Erro desconhecido';

        Page::title($message);
        Page::layout(null);

        return view('_front/error', ['status' => $status, 'message' => $message]);
    }
}
