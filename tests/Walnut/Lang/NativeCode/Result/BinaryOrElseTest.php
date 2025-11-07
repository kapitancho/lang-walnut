<?php

namespace Walnut\Lang\NativeCode\Result;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryOrElseTest extends CodeExecutionTestHelper {

	public function testIfErrorWithErrorTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError(404) ?? 504;",
			valueDeclarations: "
				makeError = ^code: Integer => Result<String, Integer> :: @code;
			"
		);
		$this->assertEquals("504", $result);
	}

	public function testIfErrorWithNoTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError(404) ?? 504;",
			valueDeclarations: "
				makeError = ^code: Integer => Result<String, Integer> :: code->asString;
			"
		);
		$this->assertEquals("'404'", $result);
	}

	public function testbinaryOrElseWithStringTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError('failed') ?? 'ERROR';",
			valueDeclarations: "
				makeError = ^msg: String => Result<Integer, String> :: @msg;
			"
		);
		$this->assertEquals("'ERROR'", $result);
	}

}
