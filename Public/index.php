<?
require_once '../Core/bootstrap.php';

use \Core\Core;

try {
    Core::$route->start();
} catch (\Throwable $e) {
    while (ob_list_handlers())
        ob_end_clean();

    header('HTTP/1.1 500 Internal Server Error', true, 500);

    echo "<h2>{$e->getMessage()}</h2>";
    echo "<h4>Path:</h4>";
    echo "<pre>{$e->getFile()}:{$e->getLine()}</pre>";
    echo "<h4>Trace:</h4>";
    echo "<pre>{$e->getTraceAsString()}</pre>";
}
