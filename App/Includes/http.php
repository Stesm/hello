<?
use \Core\Core;
use \App\Models\User;
use \Core\Helpers\Route;

#------------------------ Middleware ------------------

Route::middleware('/manage/*', function (Closure $next) {
    if (isset($_POST['login']) && isset($_POST['password']))
        if (User::authorize($_POST['login'], $_POST['password']))
            return Route::redirect($_SERVER['REQUEST_URI']);

    if (isset($_GET['logout']) && $_GET['logout'] == 'y')
        User::logout();

    if (!User::isAuthorized() && Route::instance()->path != '/register/')
        return (new \App\Controllers\AuthController())->auth();

    return $next();
});

#--------------------------- Routes --------------------

foreach (include 'routes.php' as $param) {
    if (count($param) !== 3)
        continue;

    try {
        Route::set($param[0], $param[1], $param[2]);
    } catch (\Throwable $e) {
        Core::log($e->getMessage());
    }
}
