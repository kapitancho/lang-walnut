<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UniqueTest extends CodeExecutionTestHelper {

	public function testUniqueEmpty(): void {
		$result = $this->executeCodeSnippet("[]->unique;");
		$this->assertEquals("[]", $result);
	}

	public function testUniqueNonEmptyNumbers(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5.3, 2]->unique;");
		$this->assertEquals("[1, 2, 5.3]", $result);
	}

	public function testUniqueNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("['hello','world', 'hi', 'hello']->unique;");
		$this->assertEquals("['hello', 'world', 'hi']", $result);
	}

	public function testUniqueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['hello','world', 'hi', 'hello']->unique(42);");
	}

	public function testUniqueInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[12, 'hello','world', 'hi', 'hello']->unique;");
	}
}