<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlusEmpty(): void {
		$result = $this->executeCodeSnippet("[] + [];");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryPlusNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2] + [3, 4];");
		$this->assertEquals("[1, 2, 3, 4]", $result);
	}

	public function testBinaryPlusType(): void {
		$result = $this->executeCodeSnippet("a[1, 2];",
			valueDeclarations: "a = ^arr: Array<Integer, 2..5> => Array<Integer|String, 4..7> :: arr + ['a', 'b'];"
		);
		$this->assertEquals("[1, 2, 'a', 'b']", $result);
	}

	public function testBinaryPlusInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2] + 3;");
	}
}