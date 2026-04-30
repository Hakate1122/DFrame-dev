<?php

declare(strict_types=1);

namespace DFrame\Application\Interfaces;

/**
 * **ViewEngine interface**
 *
 * This interface defines the contract for view engines in the DFrame application.
 */
interface ViewEngine
{
    /**
     * Render a template with optional data.
     *
     * @param string $template The name of the template to render.
     * @param array $data An associative array of data to pass to the template.
     * @return string The rendered template as a string.
     */
    public function render(string $template, array $data = []): string;
}
