<?php

namespace PhpMx\Front;

use PhpMx\Code;
use PhpMx\Input;
use PhpMx\Request;

class Form extends Input
{
    protected ?string $formKey = null;

    function __construct(string $name, ?array $dataValue = null)
    {
        $this->formKey = Code::on(["form-$name", url('.')]);
        parent::__construct($dataValue);
    }

    /** Verifica se o formulário foi recebido com todos os campos validados */
    function check(): bool
    {
        if (!IS_POST || Request::data('formKey') != $this->formKey)
            return false;

        return parent::check();
    }
}
