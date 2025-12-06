<?php

namespace App\Command;

class Hello
{
    public static function handle(): void
    {
        echo "Hello from custom command!\n";
    }
}
