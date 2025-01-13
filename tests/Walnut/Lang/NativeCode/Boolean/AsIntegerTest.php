<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsIntegerTest extends CodeExecutionTestHelper {

	public function testAsIntegerFalse(): void {
		$result = $this->executeCodeSnippet("false->asInteger;");
		$this->assertEquals("0", $result);
	}

	public function testAsIntegerTrue(): void {
		$result = $this->executeCodeSnippet("true->asInteger;");
		$this->assertEquals("1", $result);
	}

	public function testAsIntegerBoolean(): void {
		$result = $this->executeCodeSnippet("{#->asBoolean}->asInteger;");
		$this->assertEquals("0", $result);
	}

}