<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsIntegerTest extends CodeExecutionTestHelper {

	public function testAsIntegerOk(): void {
		$result = $this->executeCodeSnippet("'12'->asInteger;");
		$this->assertEquals("12", $result);
	}

	public function testAsIntegerInvalidInteger(): void {
		$result = $this->executeCodeSnippet("'12 days'->asInteger;");
		$this->assertEquals("@NotANumber()", $result);
	}

}