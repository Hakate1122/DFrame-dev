<?php

use PHPUnit\Framework\TestCase;
use DFrame\Application\View;
use DFrame\Attribute\Viewer as ViewAttribute;

class ViewUnitTest extends TestCase
{
    private $viewPath;
    private $view;
    protected function setUp(): void
    {
        define('ROOT_DIR', __DIR__ . '/../');
        define('INDEX_DIR', __DIR__ . '/../public/');
        $this->viewPath = ROOT_DIR . '/tests/Demo/view/';
        $this->view = new View($this->viewPath);

    }
    public function testViewRendersCorrectly()
    {
        $output = $this->view->view('demo');
        $this->assertEquals('Hello, World!', trim($output));
    }
    public function testViewWithData()
    {
        $data = ['name' => 'DFrame'];
        $output = $this->view->render('demo_with_data', $data, $this->viewPath);
        $this->assertEquals('Hello, DFrame!', trim($output));
    }

    public function testStaticRenderMethod()
    {
        $output = View::render(view: 'demo', data: [], viewPath: $this->viewPath);
        $this->assertEquals('Hello, World!', trim($output));
    }

}