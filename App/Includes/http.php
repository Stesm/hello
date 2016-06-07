<?

/** @var \Core\Helpers\Route $this */

$this->route('/', function() {
    return (new App\Controllers\AppController)->index();
});