<?
return [
    'DB' => [
        'driver' => \Core\Helpers\DBConn::class,
        'connect' => [
            'host' => 'localhost',
            'user' => 'root',
            'password' => 'asdwxzq',
            'db'   => 'asu'
        ]
    ],
    'ENV' => 'debug',
    'IMAGE_PATH' => 'img',
    'ROOT_PATH' => dirname(__DIR__),
    'PUBLIC_PATH' => dirname(__DIR__).'/Public',
    'COMPRESS_STATIC' => 1,
];
