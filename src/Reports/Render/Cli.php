<?php
namespace DFrame\Reports\Render;

use DFrame\Reports\Interface\RenderInterface;

class Cli implements RenderInterface
{
    public function render(string $type, string $message, string $file, int $line): void{

    }
}