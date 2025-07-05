<?php

namespace PhpMx\Input;

use PhpMx\Cif;
use PhpMx\Code;
use Throwable;

class InputFieldCaptcha extends InputField
{
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        parent::__construct($name, $alias, $value);

        $this->required(true, 'Informe o código de validação');

        $this->validate(function ($recived) {
            try {
                $captcha = explode('|', $recived);
                $key = Cif::off($captcha[0]);
                $value = Code::on(strtoupper($captcha[1]));
                return Code::compare($key, $value);
            } catch (Throwable $e) {
                return false;
            }
        }, 'Código de validação incorreto');
    }
}
