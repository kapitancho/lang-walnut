<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class KeyOfTest extends CodeExecutionTestHelper {

	public function testKeyOfEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->keyOf(5);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testKeyOfNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->keyOf(5);");
		$this->assertEquals("'c'", $result);
	}

	public function testKeyOfKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Result<String<1>, ItemNotFound> :: 
				m->keyOf(2);"
		);
		$this->assertEquals("'b'", $result);
	}

}