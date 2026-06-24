<?php

declare(strict_types=1);

return [
    'service' => 'gmail',
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls', // tls|ssl|none
    'auth' => true,
    'timeout' => 10,
    'ehlo_domain' => 'localhost',
    'username' => '',
    'password' => '',
    'from' => '',
    'fromname' => '',
];
