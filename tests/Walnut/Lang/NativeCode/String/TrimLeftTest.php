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

}