<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryOrElseTest extends CodeExecutionTestHelper {

	public function testBinaryOrElseWithAny(): void {
		$result = $this->executeCodeSnippet(
			"404 ?? 504;",
		);
		$this->assertEquals("404", $result);
	}

	public function testBinaryOrElseWithErrorTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError(404) ?? 504;",
			valueDeclarations: "
				makeError = ^code: Integer => Result<String, Integer> :: @code;
			"
		);
		$this->assertEquals("504", $result);
	}

	public function testBinaryOrElseWithNoTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError(404) ?? 504;",
			valueDeclarations: "
				makeError = ^code: Integer => Result<String, Integer> :: code->asString;
			"
		);
		$this->assertEquals("'404'", $result);
	}

	public function testBinaryOrElseWithStringTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError('failed') ?? 'ERROR';",
			valueDeclarations: "
				makeError = ^msg: String => Result<Integer, String> :: @msg;
			"
		);
		$this->assertEquals("'ERROR'", $result);
	}

}
