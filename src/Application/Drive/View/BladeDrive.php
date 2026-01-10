<?php
// not available yet
namespace DFrame\Application\Drive\View;

use DFrame\Application\Interfaces\ViewEngine;

/**
 * BladeDrive View Engine using Blade templating engine.
 */
class BladeDrive implements ViewEngine
{
    protected $viewPath;
    protected $options;

    public function __construct($viewPath, $options = [])
    {
        $this->viewPath = $viewPath;
        $this->options = $options;
    }

    public function render(string $template, array $data = []): string
    {
        // Triển khai thực tế với package blade
        throw new \Exception('BladeDrive: Please install blade package and implement render()');
    }
}