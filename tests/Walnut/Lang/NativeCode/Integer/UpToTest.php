<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UpToTest extends CodeExecutionTestHelper {

	public function testUpTo(): void {
		$result = $this->executeCodeSnippet("3->upTo(5);");
		$this->assertEquals("[3, 4, 5]", $result);
	}

	public function testUpToEmpty(): void {
		$result = $this->executeCodeSnippet("3->upTo(1);");
		$this->assertEquals("[]", $result);
	}

	public function testUpToType(): void {
		$result = $this->executeCodeSnippet(
			"myUpTo(6);",
			valueDeclarations: "myUpTo = ^n: Integer<4..7> => Array<Integer<2..7>, 3..6> :: 2->upTo(n);"
		);
		$this->assertEquals("[2, 3, 4, 5, 6]", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3->upTo('hello');");
	}

}