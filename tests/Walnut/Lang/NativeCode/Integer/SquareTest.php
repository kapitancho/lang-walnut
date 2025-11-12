<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SquareTest extends CodeExecutionTestHelper {

	public function testSquarePositive(): void {
		$result = $this->executeCodeSnippet("3->square;");
		$this->assertEquals("9", $result);
	}

	public function testSquareNegative(): void {
		$result = $this->executeCodeSnippet("-4->square;");
		$this->assertEquals("16", $result);
	}

	public function testSquareNegativeRange(): void {
		$result = $this->executeCodeSnippet(
			"f(-11);",
			valueDeclarations: "f = ^num: Integer<-20..-3> => Integer<9..400> :: num->square;"
		);
		$this->assertEquals("121", $result);
	}

	public function testSquareInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: String['hello']",
			"3->square('hello');"
		);
	}

}