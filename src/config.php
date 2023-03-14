<?php

$config = array(
    'rewrite' => array(
        //设置模块 碰到 http://{host}/admin/ 认为进入了后台模块 数组 0 标识默认 m
        'm' => ['web', 'admin', 'app', 'api'],
        'c' => 'main', //controller 默认值
        'a' => 'index', //action 默认值,
        '_p' => '', //param 默认值,
        'isRewrite' => true //是否开启伪静态 .htaccess 文件配置
    ),
    'debug' => true,
    'plugins' => ['include', 'plugin', 'jobs','service','lang'], //扩展目录
    'static' => "res",
    'logPath' => 'logs', //日志路径，请保证路径权限可写
    'startSession' => false, //session 默认不开启
    'limitMax' => 1000 //保护数据库以免一失误导致大查询
);

$dbb = array(
    'db' =>//ORM
        [
            'connections' => [
                'default' => [
                    "driver" => "mysql",
                    "host" => "127.0.0.1",
                    "database" => "brook3_master",
                    "username" => "root",
                    "password" => "root",
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ],
                // $res = DB::connection('slave')->table('user')->where('id', 1)->first();
                'slave' => [
                    "driver" => "mysql",
                    "host" => "127.0.0.1",
                    "database" => "brook3_master",
                    "username" => "root",
                    "password" => "root",
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ],
            ]
        ],
    'mysql' => [
        //主库
        'master' => [
            'MYSQL_HOST' => '127.0.0.1',
            'MYSQL_PORT' => '3306',
            'MYSQL_USER' => 'root',
            'MYSQL_DB' => 'db_test',
            'MYSQL_PASS' => '',
            'MYSQL_CHARSET' => 'utf8',
        ],
        //从库可以加入多个实例
        'slave' => [
            'MYSQL_HOST' => '127.0.0.1',
            'MYSQL_PORT' => '3306',
            'MYSQL_USER' => 'root',
            'MYSQL_DB' => 'db_test',
            'MYSQL_PASS' => '123456',
            'MYSQL_CHARSET' => 'utf8',
        ]
    ],
    'prefix' => 'tb_',
);
$app = [
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 20,
        'auth' => '',
        'database' => 6,
        'prefix' => 'redis_session:'
    ],

];

return $dbb + $config + $app;
