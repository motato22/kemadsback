<?php

/*
|--------------------------------------------------------------------------
| Configuración de Laravel Horizon para el sistema de publicidad
|--------------------------------------------------------------------------
| Agregar los workers del sistema de publicidad en config/horizon.php
| dentro del array 'environments' > 'production' y 'local'.
|
| Los workers están organizados por prioridad y throughput esperado.
*/

return [

    /*
     | Agregar en config/horizon.php > environments > production:
     |
     | 'adv-heartbeats' => [
     |     'connection' => 'redis',
     |     'queue'      => ['heartbeats'],
     |     'balance'    => 'auto',
     |     'minProcesses' => 2,
     |     'maxProcesses' => 8,
     |     'tries'      => 3,
     |     'timeout'    => 30,
     |     'memory'     => 128,
     | ],
     |
     | 'adv-alerts' => [
     |     'connection' => 'redis',
     |     'queue'      => ['alerts'],
     |     'balance'    => 'simple',
     |     'minProcesses' => 1,
     |     'maxProcesses' => 3,
     |     'tries'      => 2,
     |     'timeout'    => 60,
     |     'memory'     => 128,
     | ],
     |
     | 'adv-exports' => [
     |     'connection' => 'redis',
     |     'queue'      => ['exports'],
     |     'balance'    => 'simple',
     |     'minProcesses' => 1,
     |     'maxProcesses' => 2,
     |     'tries'      => 2,
     |     'timeout'    => 300,
     |     'memory'     => 256,
     | ],
     |
     | 'adv-default' => [
     |     'connection' => 'redis',
     |     'queue'      => ['default'],
     |     'balance'    => 'simple',
     |     'minProcesses' => 1,
     |     'maxProcesses' => 4,
     |     'tries'      => 3,
     |     'timeout'    => 60,
     |     'memory'     => 128,
     | ],
    */

    'supervisor_groups' => [

        'production' => [

            'adv-heartbeats' => [
                'connection'   => 'redis',
                'queue'        => ['heartbeats'],
                'balance'      => 'auto',
                'minProcesses' => 2,
                'maxProcesses' => 8,
                'tries'        => 3,
                'timeout'      => 30,
                'memory'       => 128,
            ],

            'adv-alerts' => [
                'connection'   => 'redis',
                'queue'        => ['alerts'],
                'balance'      => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 3,
                'tries'        => 2,
                'timeout'      => 60,
                'memory'       => 128,
            ],

            'adv-exports' => [
                'connection'   => 'redis',
                'queue'        => ['exports'],
                'balance'      => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'tries'        => 2,
                'timeout'      => 300,
                'memory'       => 256,
            ],

            'adv-default' => [
                'connection'   => 'redis',
                'queue'        => ['default'],
                'balance'      => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 4,
                'tries'        => 3,
                'timeout'      => 60,
                'memory'       => 128,
            ],
        ],

        'local' => [
            'adv-local-worker' => [
                'connection'   => 'redis',
                'queue'        => ['heartbeats', 'alerts', 'exports', 'default'],
                'balance'      => 'simple',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'tries'        => 3,
                'timeout'      => 60,
                'memory'       => 128,
            ],
        ],
    ],
];
