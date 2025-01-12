<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class TrimRightTest extends CodeExecutionTestHelper {

	public function testTrimRight(): void {
		$result = $this->executeCodeSnippet("'   hello   '->trimRight;");
		$this->assertEquals("'   hello'", $result);
	}

	public function testTrimRightChar(): void {
		$result = $this->executeCodeSnippet("'** hello **'->trimRight('*');");
		$this->assertEquals("'** hello '", $result);
	}

}