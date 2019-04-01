<?
namespace App\Controllers;

use Core\Core;
use Core\Helpers\Scud;

/**
 * Class AppController
 * @package App\Controllers
 */
class AppController extends FrontEndController
{
    public function before()
    {
        parent::before();

        Scud::registerDataSource('ServicesDataSource', 'components/services-list');
    }

    /**
     * @return string
     */
    public static function index(){
        return Core::view('index');
    }
}
