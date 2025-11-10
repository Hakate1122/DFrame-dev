<?php
namespace DFrame\Attribute;

use DFrame\Application\View;

/**
 * #### Attribute View for method rendering
 *
 * Attribute View class for rendering views with data in methods.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Viewer
{
    public ?string $view;
    public ?string $viewPath;
    public array $data;

    public function __construct(?string $view = null, array $data = [], ?string $viewPath = null)
    {
        $this->view = $view;
        $this->viewPath = $viewPath;
        $this->data = $data;
    }

    /**
     * Handle a method result and convert it to rendered HTML when appropriate.
     *
     * Behavior:
     * - string  => treated as view name (unless template provided)
     * - array   => treated as data for a view (template must be provided or defaults to 'index')
     * - null    => will render provided template if any, otherwise returns null
     * - other   => returned unchanged
     *
     * @param mixed $result
     * @return mixed|string|null
     */
    public function handle($result)
    {
        // If user returned a string => treat as view name
        if (is_string($result)) {
            $viewName = $this->view ?? $result;
            return View::render($viewName, [], $this->viewPath);
        }

        // If user returned an array => treat as data for view
        if (is_array($result)) {
            $viewName = $this->view ?? 'index';
            return View::render($viewName, $result, $this->viewPath);
        }

        // If null => render view if provided
        if ($result === null && $this->view !== null) {
            return View::render($this->view, [], $this->viewPath);
        }

        // Other types (Response object, scalar not intended as view, etc.) pass through
        return $result;
    }
}