<?php

namespace Walnut\Lang\Test\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class TrimLeftTest extends CodeExecutionTestHelper {

	public function testTrimLeft(): void {
		$result = $this->executeCodeSnippet("'   hello   '->trimLeft;");
		$this->assertEquals("'hello   '", $result);
	}

	public function testTrimLeftChar(): void {
		$result = $this->executeCodeSnippet("'** hello **'->trimLeft('*');");
		$this->assertEquals("' hello **'", $result);
	}

	public function testTrimLeftInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"'** hello **'->trimLeft(42);");
	}

}