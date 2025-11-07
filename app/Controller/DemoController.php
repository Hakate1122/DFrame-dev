<?php

namespace App\Controller;

use DFrame\Application\Router;
use DFrame\Application\Route;
use DFrame\Application\View;
use Gregwar\Captcha\CaptchaBuilder;

class DemoController extends Controller
{

    #[Router(path: '/haha', method: 'GET', isApi: false, name: 'demo.haha', middleware: null)]
    public function demo()
    {
        return "oke";
    }
    #[Route(path: '/captcha', method: 'GET', name: 'show.captcha')]
    public function captcha()
    {
        $builder = new CaptchaBuilder();
        $builder->build();

        // Lưu mã CAPTCHA vào session
        $_SESSION['captcha'] = $builder->getPhrase();

        return View::render('captcha', ['builder' => $builder]);
    }
    #[Route(path: '/api/verify-captcha', method: 'POST', name: 'verify.captcha', isApi: true)]
    public function verifyCaptcha()
    {
        $captcha = $_POST['captcha'] ?? '';
        if ($captcha === $_SESSION['captcha']) {
            // CAPTCHA đúng
            return "CAPTCHA đúng";
        } else {
            // CAPTCHA sai
            return "CAPTCHA sai";
        }
    }
}
