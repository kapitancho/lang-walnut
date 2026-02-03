<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class DownToTest extends CodeExecutionTestHelper {

	public function testDownToEmpty(): void {
		$result = $this->executeCodeSnippet("3->downTo(5);");
		$this->assertEquals("[]", $result);
	}

	public function testDownTo(): void {
		$result = $this->executeCodeSnippet("3->downTo(1);");
		$this->assertEquals("[3, 2, 1]", $result);
	}

	public function testDownToType(): void {
		$result = $this->executeCodeSnippet(
			"myDownTo(6);",
			valueDeclarations: "myDownTo = ^n: Integer<4..7> => Array<Integer<2..7>, 3..6> :: n->downTo(2);"
		);
		$this->assertEquals("[6, 5, 4, 3, 2]", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3->downTo('hello');");
	}

}