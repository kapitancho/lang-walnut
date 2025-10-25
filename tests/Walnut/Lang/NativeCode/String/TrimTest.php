<?php

namespace Walnut\Lang\Test\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class TrimTest extends CodeExecutionTestHelper {

	public function testTrim(): void {
		$result = $this->executeCodeSnippet("'   hello   '->trim;");
		$this->assertEquals("'hello'", $result);
	}

	public function testTrimChar(): void {
		$result = $this->executeCodeSnippet("'** hello **'->trim('*');");
		$this->assertEquals("' hello '", $result);
	}

}