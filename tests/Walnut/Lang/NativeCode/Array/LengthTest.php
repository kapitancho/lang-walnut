<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class LengthTest extends CodeExecutionTestHelper {

	public function testLengthEmpty(): void {
		$result = $this->executeCodeSnippet("[]->length;");
		$this->assertEquals("0", $result);
	}

	public function testLengthNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->length;");
		$this->assertEquals("2", $result);
	}

	public function testLengthOfTupleWithRest(): void {
		$result = $this->executeCodeSnippet(
			"g[1, 2, 'hello'];",
			valueDeclarations: "g = ^p: [Integer, Real, ...String] => Integer<2..> :: p->length;"
		);
		$this->assertEquals("3", $result);
	}
}