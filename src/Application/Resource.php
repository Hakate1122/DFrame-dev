<?php

declare(strict_types=1);

namespace DFrame\Application;

class Resource
{
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = rtrim(
            $basePath ?? ROOT_DIR . '/resource',
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;
    }

    public function get(string $path): string
    {
        $fullPath = realpath($this->basePath . $path);

        if (
            $fullPath === false ||
            !str_starts_with($fullPath, realpath($this->basePath)) ||
            !is_file($fullPath)
        ) {
            throw new \RuntimeException("Resource not found or invalid: {$path}");
        }

        return file_get_contents($fullPath);
    }

    public static function css(string $name): string
    {
        return (new self())->get("css/{$name}.css");
    }

    public static function js(string $name): string
    {
        return (new self())->get("js/{$name}.js");
    }

    public static function scss(string $name): string
    {
        return (new self())->get("scss/{$name}.scss");
    }

    public static function ts(string $name): string
    {
        return (new self())->get("ts/{$name}.ts");
    }
}
