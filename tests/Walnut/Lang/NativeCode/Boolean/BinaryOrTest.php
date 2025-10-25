<?php

namespace Walnut\Lang\Test\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryOrTest extends CodeExecutionTestHelper {

	public function testBinaryOrFalseFalse(): void {
		$result = $this->executeCodeSnippet("false || false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryOrFalseTrue(): void {
		$result = $this->executeCodeSnippet("false || true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrTrueFalse(): void {
		$result = $this->executeCodeSnippet("false || true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrTrueTrue(): void {
		$result = $this->executeCodeSnippet("true || true;");
		$this->assertEquals("true", $result);
	}

}