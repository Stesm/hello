<?php
spl_autoload_register(function($class){
    if(is_file($path = str_replace('\\', '/', APP_ROOT."/{$class}.php"))){
        require_once($path);
    }
});

use \Core\Core;
define('APP_ROOT', dirname(dirname(__FILE__)));
define('PUBLIC_ROOT', APP_ROOT.'/Public');

spl_autoload_register(function($class){
    $path = str_replace('\\', '/', APP_ROOT."/Core/{$class}.php");
    $path = str_replace('Egulias', 'Vendor', $path);

    if(is_file($path)){
        require_once($path);
    }
});

if(is_file($http = APP_ROOT.'/App/Includes/http.php'))
    require_once $http;

if(file_exists($data_sources = APP_ROOT.'/App/Includes/data.sources.php'))
    require_once $data_sources;

Core::load();
