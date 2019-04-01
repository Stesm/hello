#!/usr/bin/env php
<?php

use \Core\Core;
define('APP_ROOT', dirname(__FILE__));

spl_autoload_register(function($class){
    if(is_file($path = str_replace('\\', '/', APP_ROOT."/{$class}.php"))){
        require_once($path);
    }else
        die("Class {$class} not found in path {$path}");
});

Core::load();

try {
    $command = new \Core\Commands\Cmd($argv);
    $command->launch();
} catch (\Throwable $e) {
    echo "\n{$e->getMessage()}\n\n";
}
