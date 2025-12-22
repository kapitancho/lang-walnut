<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\AST\Parser\StringEscapeCharHandler;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\TemplatePrecompiler;

final class TemplatePrecompilerTest extends TestCase {

	public function testSourcePathWithTest(): void {
		$result = new TemplatePrecompiler(new StringEscapeCharHandler())->determineSourcePath("path");
		$this->assertEquals('path.nut.html', $result);
	}

	public function testOk(): void {
		$result = new TemplatePrecompiler(new StringEscapeCharHandler())->precompileSourceCode("modx", "^<!-- Tpl %% A --> template");
		$this->assertStringContainsString("UnableToRenderTemplate", $result);
	}

}