<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

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