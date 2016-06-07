<?
namespace App\Controllers;

use Core\Core;

/**
 * Class AppController
 * @package App\Controllers
 */
class AppController {

    /**
     * @return string
     */
    public function index(){
        return Core::view('index');
    }
}