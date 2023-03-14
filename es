#!/usr/bin/env -S-P/usr/local/bin:/usr/bin:${PATH} php
<?php

define('APP_DIR', realpath('./'));
//加载composer 信息
if(file_exists(__DIR__.'/vendor/autoload.php')){
    require_once __DIR__.'/vendor/autoload.php';
}
//能处理shell 请求
if (!empty($argc)) {
    $_REQUEST['m'] = 'shell';
    $_REQUEST['c'] = $argv[1] ?? 'job';
    $_REQUEST['a'] = $argv[2] ?? 'index';
    $_REQUEST['p'] = empty($argv[3])? '': $argv[3];
}else{
    exit('只支持shell运行');
}

require(APP_DIR . '/src/core/es.php');
