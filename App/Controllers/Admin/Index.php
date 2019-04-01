<?
namespace App\Controllers\Admin;

use Core\Core;

/**
 * Class AppController
 * @package App\Controllers
 */
class Index {

    /**
     * @return string
     */
    public static function index(){
        return Core::view('admin/index');
    }
}