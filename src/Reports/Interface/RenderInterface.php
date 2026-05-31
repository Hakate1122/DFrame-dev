<?php

declare(strict_types=1);

namespace DLight\Reports\Interface;

/**
 * RenderInterface - Interface for renderers
 */
interface RenderInterface
{
    public function render(string $type, string $message, string $file, int $line, array $context = []): void;
}
