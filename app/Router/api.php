<?php

$router = new DFrame\Application\Router();

$router->signApi('GET /', function () {
    return "Hello, World!";
})->name('api.home');

$router->signApi('GET /demo/mail', function () {
    $mail = new DFrame\Application\Mail();
    $mail   ->to('datd5400@gmail.com')
            ->subject('Test Email from DFrame')
            ->body('This is a test email sent from DFrame.')
            ->send();
    return 'Email sent successfully!';
})->name('api.demo.mail');