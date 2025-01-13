<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryAndTest extends CodeExecutionTestHelper {

	public function testBinaryAndFalseFalse(): void {
		$result = $this->executeCodeSnippet("false && false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndFalseTrue(): void {
		$result = $this->executeCodeSnippet("false && true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndTrueFalse(): void {
		$result = $this->executeCodeSnippet("false && true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndTrueTrue(): void {
		$result = $this->executeCodeSnippet("true && true;");
		$this->assertEquals("true", $result);
	}

}