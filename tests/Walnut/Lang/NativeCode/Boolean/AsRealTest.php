<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsRealFalse(): void {
		$result = $this->executeCodeSnippet("false->asReal;");
		$this->assertEquals("0", $result);
	}

	public function testAsRealTrue(): void {
		$result = $this->executeCodeSnippet("true->asReal;");
		$this->assertEquals("1", $result);
	}

	public function testAsRealBoolean(): void {
		$result = $this->executeCodeSnippet("{#->asBoolean}->asReal;");
		$this->assertEquals("0", $result);
	}

}