<?
namespace App\Controllers;

use Core\Core;

/**
 * Class AuthController
 * @package App\Controllers
 */
class AuthController extends FrontEndController {

    /**
     * @return string
     */
    public function auth(){
        return Core::view('admin/auth');
    }
}
