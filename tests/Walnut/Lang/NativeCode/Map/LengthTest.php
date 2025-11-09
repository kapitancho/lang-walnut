<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class LengthTest extends CodeExecutionTestHelper {

	public function testLengthEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->length;");
		$this->assertEquals("0", $result);
	}

	public function testLengthNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->length;");
		$this->assertEquals("2", $result);
	}

	public function testLengthNonEmptyRange(): void {
		$result = $this->executeCodeSnippet(
			"[a: 1, b: 2]->length;",
			valueDeclarations: "myLength = ^m: Map<3..5> => Integer<3..5> :: m->length;",
		);
		$this->assertEquals("2", $result);
	}
}