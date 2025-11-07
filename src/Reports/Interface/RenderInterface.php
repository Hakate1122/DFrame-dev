<?php
namespace DFrame\Reports\Interface;

interface RenderInterface
{
    public function render(string $type, string $message, string $file, int $line): void;
}
