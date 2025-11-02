<?php
namespace Core\Reports\Render;

use Core\Reports\Interface\RenderInterface;

class Cli implements RenderInterface
{
    public function render(string $type, string $message, string $file, int $line): void{

    }
}