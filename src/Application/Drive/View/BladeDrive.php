<?php

declare(strict_types=1);

namespace DLight\Application\Drive\View;

use DLight\Application\Interfaces\ViewEngine;

/**
 * BladeDrive View Engine using Blade templating engine.
 */
class BladeDrive implements ViewEngine
{
    public function __construct(protected $viewPath, protected $options = [])
    {
    }

    public function render(string $template, array $data = []): string
    {
        throw new \Exception('BladeDrive: Please install blade package and implement render()');
    }
}
