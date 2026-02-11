<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class IfErrorTest extends CodeExecutionTestHelper {

	public function testIfErrorWithAny(): void {
		$result = $this->executeCodeSnippet(
			"404->ifError(^e: Integer => Integer :: e + 100);",
		);
		$this->assertEquals("404", $result);
	}

	public function testIfErrorWithErrorTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError(404)->ifError(^e: Integer => Integer :: e + 100);",
			valueDeclarations: "
				makeError = ^code: Integer => Result<String, Integer> :: @code;
			"
		);
		$this->assertEquals("504", $result);
	}

	public function testIfErrorWithStringTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeError('failed')->ifError(^e: String => String :: 'ERROR: ' + e);",
			valueDeclarations: "
				makeError = ^msg: String => Result<Integer, String> :: @msg;
			"
		);
		$this->assertEquals("'ERROR: failed'", $result);
	}

	public function testIfErrorInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"makeError('x')->ifError(123);",
			valueDeclarations: "
				makeError = ^s: String => Result<Integer, String> :: @s;
			"
		);
	}

	public function testIfErrorInvalidCallbackParameterTypeForResult(): void {
		$this->executeErrorCodeSnippet("The parameter type String of the callback function is not a subtype of Integer",
			"makeError('x')->ifError(^e: Integer => Integer :: e);",
			valueDeclarations: "
				makeError = ^s: String => Result<Integer, String> :: @s;
			"
		);
	}

	public function testIfErrorInvalidCallbackParameterTypeForAny(): void {
		$this->executeErrorCodeSnippet("The parameter type Any of the callback function is not a subtype of Integer",
			"makeError('x')->ifError(^e: Integer => Integer :: e);",
			valueDeclarations: "
				makeError = ^s: String => Any :: @s;
			"
		);
	}

}
