<?php

namespace Core\Application\Drive\View;

use Core\Application\Interfaces\ViewEngine;
use eftec\bladeone\BladeOne;

/**
 * #### Class BladeOneDrive for BladeOne view engine
 */
class BladeOneDrive implements ViewEngine
{
    protected $blade;

    public function __construct($viewPath, $options = [])
    {
        // Respect explicit "cache" option; if false => disable cache
        $useCache = $options['cache'] ?? true;

        $cachePath = null;
        if ($useCache) {
            // prefer cache_path, fallback to compiled_path for backward compatibility
            $cachePath = $options['cache_path'] ?? $options['compiled_path'] ?? (defined('INDEX_DIR') ? INDEX_DIR . 'cache/' : null);
        } else {
            $cachePath = null;
        }

        // Ensure cache dir exists and is writable
        if ($cachePath) {
            if (!is_dir($cachePath)) {
                if (false === @mkdir($cachePath, 0777, true)) {
                    throw new \Exception("BladeOneDrive: Unable to create cache directory: $cachePath");
                }
            }
            if (!is_writable($cachePath)) {
                @chmod($cachePath, 0777);
            }
        }

        $mode = $cachePath ? BladeOne::MODE_AUTO : BladeOne::MODE_SLOW;

        $this->blade = new BladeOne($viewPath, $cachePath, $mode);
    }

    public function render(string $template, array $data = []): string
    {
        return $this->blade->run($template, $data);
    }
}
