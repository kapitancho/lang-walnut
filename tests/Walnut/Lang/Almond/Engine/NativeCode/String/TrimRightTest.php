<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class TrimRightTest extends CodeExecutionTestHelper {

	public function testTrimRight(): void {
		$result = $this->executeCodeSnippet("'   hello   '->trimRight;");
		$this->assertEquals("'   hello'", $result);
	}

	public function testTrimRightChar(): void {
		$result = $this->executeCodeSnippet("'** hello **'->trimRight('*');");
		$this->assertEquals("'** hello '", $result);
	}

	public function testTrimRightInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"'** hello **'->trimRight(42);");
	}

}