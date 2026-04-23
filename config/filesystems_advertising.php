<?php

/*
|--------------------------------------------------------------------------
| Configuración del disco Cloudflare R2 para el sistema de publicidad
|--------------------------------------------------------------------------
| Agregar este fragmento dentro del array 'disks' en config/filesystems.php:
|
|   'r2' => [
|       'driver'   => 's3',
|       'key'      => env('CLOUDFLARE_R2_ACCESS_KEY_ID'),
|       'secret'   => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
|       'region'   => env('CLOUDFLARE_R2_DEFAULT_REGION', 'auto'),
|       'bucket'   => env('CLOUDFLARE_R2_BUCKET'),
|       'endpoint' => env('CLOUDFLARE_R2_ENDPOINT'),
|       'url'      => env('CLOUDFLARE_R2_URL'),
|       'use_path_style_endpoint' => true,
|       'throw'    => false,
|   ],
|
| También agregar en config/filesystems.php > 'links':
|   (no aplica para R2, las URLs firmadas se generan dinámicamente)
|
| Dependencia requerida:
|   composer require league/flysystem-aws-s3-v3
*/

return [
    'r2' => [
        'driver'                  => 's3',
        'key'                     => env('CLOUDFLARE_R2_ACCESS_KEY_ID'),
        'secret'                  => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
        'region'                  => env('CLOUDFLARE_R2_DEFAULT_REGION', 'auto'),
        'bucket'                  => env('CLOUDFLARE_R2_BUCKET'),
        'endpoint'                => env('CLOUDFLARE_R2_ENDPOINT'),
        'url'                     => env('CLOUDFLARE_R2_URL'),
        'use_path_style_endpoint' => true,
        'throw'                   => false,
    ],
];
