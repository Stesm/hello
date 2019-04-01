<?php
namespace App\Controllers;

use Core\Helpers\PageAssets;
use Core\Prototypes\Controller;

class FrontEndController extends Controller
{
    public function before()
    {
        PageAssets::pushArray([
            '/css/swiper.css',
            '/css/main.min.css',
            '/js/main.min.js'
        ]);
    }
}
