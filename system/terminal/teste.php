<?php

use PhpMx\Terminal;

return new class {
    function __invoke()
    {
        // // INPUT
        // $name = Terminal::input('Nome', default: 'André', required: true);
        // Terminal::echol('[#c:s,Você digitou:] [#]', [$name]);

        // // PASSWORD
        // $pass = Terminal::password('Senha', expected: null, required: true);
        // Terminal::echol('[#c:s,Senha recebida com] [#c:p,#] [#c:s,caracteres]', [strlen($pass)]);

        $options = [
            'dev' => 'Desenvolvimento',
            'hom' => 'Homologação',
            'prod' => 'Produção',
            'dev2' => 'Development',
            'hom2' => 'Homologation',
            'prod2' => 'Production',
            'dev3' => 'Développement',
            'hom3' => 'Homologation FR',
            'prod3' => 'Production FR',
            'dev4' => 'Entwicklung',
            'hom4' => 'Homologierung',
            'prod4' => 'Produktion',
            'dev5' => 'Desarrollo',
            'hom5' => 'Homologación',
            'prod5' => 'Producción',
            'dev6' => 'Développement 2',
            'hom6' => 'Homologação 2',
            'prod6' => 'Produção 2',
            'dev7' => 'Ambiente Local',
            'hom7' => 'Ambiente Remoto',
            'prod7' => 'Ambiente Cloud',
            'dev8' => 'Servidor A',
            'hom8' => 'Servidor B',
            'prod8' => 'Servidor C',
        ];

        // SELECT
        // $choice = Terminal::select(
        //     'Escolha um ambiente',
        //     options: $options,
        //     default: 'dev6'
        // );
        // Terminal::echol('[#c:s,Ambiente escolhido:] [#c:p,#]', [$options[$choice]]);

        // // CONFIRM
        // $ok = Terminal::confirm('Confirmar deploy?', default: true);
        // if ($ok) {
        //     Terminal::echol('[#c:s,Deploy confirmado!]');
        // } else {
        //     Terminal::echol('[#c:e,Deploy cancelado.]');
        // }

        // TABLE
        Terminal::table([
            ['Ambiente', 'Status', 'Versão'],
            ['Desenvolvimento', '[#c:s,OK]', 'v1.2.0'],
            ['Homologação', '[#c:w,Warn]', 'v1.1.5'],
            ['Produção', '[#c:e,Err]', 'v1.0.0'],
        ], hasHeader: true);
    }
};
