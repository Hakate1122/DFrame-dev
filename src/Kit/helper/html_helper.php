<?php
if (!function_exists('js')) {
    /**
     * Generate a script tag for a JavaScript file.
     *
     * @param string $path The path to the JavaScript file.
     * @return string The HTML script tag for the JavaScript file.
     */
    function js($path)
    {
        return '<script src="' . $path . '"></script>';
    }
}

if (!function_exists('css')) {
    /**
     * Generate a link tag for a CSS file.
     *
     * @param string $path The path to the CSS file.
     * @return string The HTML link tag for the CSS file.
     */
    function css($path)
    {
        return '<link rel="stylesheet" href="' . $path . '">';
    }
}

if (!function_exists('jslog')) {
    /**
     * Log data to the browser console for debugging purposes.
     *
     * @param mixed $data The data to be logged. It will be converted to JSON format.
     */
    function jslog($data): void
    {
        echo '<script>';
        echo 'console.log(' . json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        ) . ');';
        echo '</script>';
    }
}
