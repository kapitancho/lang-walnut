<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ReverseTest extends CodeExecutionTestHelper {

	public function testReverse(): void {
		$result = $this->executeCodeSnippet("'hello'->reverse;");
		$this->assertEquals("'olleh'", $result);
	}

	public function testReverseRangeChecj(): void {
		$result = $this->executeCodeSnippet("rev('hello');",
			valueDeclarations: "rev = ^s: String<4..6> => String<4..6> :: s->reverse;"
		);
		$this->assertEquals("'olleh'", $result);
	}

	public function testReverseSubsetType(): void {
		$result = $this->executeCodeSnippet("rev('hello');",
			valueDeclarations: "rev = ^s: String['hello', 'world'] => String['olleh', 'dlrow'] :: s->reverse;"
		);
		$this->assertEquals("'olleh'", $result);
	}

}