<?php
// not available yet
namespace DFrame\Application\Drive\View;

use DFrame\Application\Interfaces\ViewEngine;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use \Twig\TwigFunction;

/**
 * TwigDrive class for rendering Twig templates.
 */
class TwigDrive implements ViewEngine
{
    protected $twig;

    public function __construct($viewPath, $options = [])
    {
        $loader = new FilesystemLoader($viewPath);
        $this->twig = new Environment($loader, $options);

        // Đăng ký các function từ config
        if (!empty($options['functions']) && is_array($options['functions'])) {
            foreach ($options['functions'] as $name => $callable) {
                $this->twig->addFunction(new TwigFunction($name, $callable));
            }
        }
    }

    public function render(string $template, array $data = []): string
    {
        // Tự động thêm đuôi .twig nếu chưa có
        if (!str_ends_with($template, '.twig')) {
            $template .= '.twig';
        }
        return $this->twig->render($template, $data);
    }
}