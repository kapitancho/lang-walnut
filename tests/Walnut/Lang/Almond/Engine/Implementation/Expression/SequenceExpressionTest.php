<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SequenceExpressionTest extends CodeExecutionTestHelper {

	public function testSequence(): void {
		$result = $this->executeCodeSnippet("{1; 2};");
		$this->assertEquals("2", $result);
	}

	public function testSequenceEmpty(): void {
		$result = $this->executeCodeSnippet("{};");
		$this->assertEquals("null", $result);
	}

	public function testSequenceReturn(): void {
		$result = $this->executeCodeSnippet("{=> 1; 2};");
		$this->assertEquals("1", $result);
	}

}