<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryXorTest extends CodeExecutionTestHelper {

	public function testBinaryXorFalseFalse(): void {
		$result = $this->executeCodeSnippet("false ^^ false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryXorFalseTrue(): void {
		$result = $this->executeCodeSnippet("false ^^ true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryXorTrueFalse(): void {
		$result = $this->executeCodeSnippet("false ^^ true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryXorTrueTrue(): void {
		$result = $this->executeCodeSnippet("true ^^ true;");
		$this->assertEquals("false", $result);
	}

}