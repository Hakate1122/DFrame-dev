<?php
namespace App\Controller;

use Core\Application\Router;

class DemoController extends Controller{

    #[Router(path:'/haha', method:'GET', isApi:false, name:'demo.haha', middleware: null)]
    public function demo(){
        return "oke";
    }
}