<?php
declare(strict_types=1);

namespace Mgrn\Scoperender\Test;

use Mgrn\Scoperender\Exception;
use Mgrn\Scoperender\ScopeRenderer;
use Mgrn\Scoperender\Test\Scopes\SimpleScope;
use PHPUnit\Framework\TestCase;

class ScopeRenderTest extends TestCase
{

    protected ScopeRenderer $scopeRenderer;

    public function setUp(): void
    {
        $partialDir = __DIR__.DIRECTORY_SEPARATOR.'partials';
        $this->scopeRenderer = new ScopeRenderer($partialDir);
    }

    public function testBasicScope1(): void
    {
        $scope = new SimpleScope(['basicString'=>'Hello']);
        $output = $this->scopeRenderer->render('simple',$scope);
        $this->assertIsString($output);
        $this->assertEquals('Hello',$output);
        ob_end_clean();
    }

    public function testBasicScope2(): void
    {
        $scope = new SimpleScope(['basicString'=>'<script>Hello</script>']);
        $output = $this->scopeRenderer->render('simple2',$scope);
        $this->assertIsString($output);
        $this->assertEquals('<div>&lt;script&gt;Hello&lt;/script&gt;</div>',$output);
        ob_end_clean();
    }

    public function testBasicScope3(): void
    {
        ob_start();
        echo 'h';
        $scope = new SimpleScope(['basicString'=>'Hello']);
        $output = $this->scopeRenderer->render('simple2',$scope);
        $this->assertIsString($output);
        $this->assertEquals('<div>Hello</div>',$output);
        $outputPre = ob_get_clean();
        $this->assertEquals('h',$outputPre);
        ob_end_clean();
    }

    public function testBasicScope4(): void
    {
        $scope = new SimpleScope(['basicString'=>'Hello']);
        $output = $this->scopeRenderer->render('sub/simple',$scope);
        $this->assertIsString($output);
        $this->assertEquals('sub Hello',$output);
        ob_end_clean();
    }

    public function testBasicScope5(): void
    {
        $this->expectException(Exception::class);
        try {
            $partialDir = __DIR__ . DIRECTORY_SEPARATOR . 'partials/sub';
            $scopeRenderer = new ScopeRenderer($partialDir);
            $scope = new SimpleScope(['basicString' => 'Hello']);
            $output = $scopeRenderer->render('../simple', $scope);
        }
        catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }
}