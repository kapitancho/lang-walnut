<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MethodCallExpressionTest extends CodeExecutionTestHelper {

	public function testMethodCall(): void {
		$result = $this->executeCodeSnippet("3.14->asInteger;");
		$this->assertEquals("3", $result);
	}

	public function testMethodCallAs(): void {
		$result = $this->executeCodeSnippet("3.14->as(type{Integer});");
		$this->assertEquals("3", $result);
	}

	public function testMethodUnknown(): void {
		$this->executeErrorCodeSnippet('Cannot call method', "3.14->doMagic;");
	}

}